<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAnnotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Public Feed of Documents
     */
    public function index(Request $request)
    {
        $query = Document::where('is_public', true);

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $documents = $query->latest()->get();
        return view('documents.index', compact('documents'));
    }

    /**
     * Show Upload Form
     */
    public function create()
    {
        return view('documents.create');
    }

    /**
     * Store & Process Document (OCR + AI Classification)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:20480', // 20MB Max
            'country' => 'required|string',
        ]);

        try {
            Log::info("--- STARTING DOCUMENT UPLOAD ---");

            // 1. Upload File
            $path = $request->file('file')->store('documents', 'public');
            Log::info("File stored at: " . $path);

            // 2. Create Initial Record
            $document = Document::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'file_path' => $path,
                'country' => $request->country,
                'type' => 'Processing...',
                'is_public' => true,
            ]);

            // 3. Azure Document Intelligence (Extract Text)
            //$extractedText = $this->extractTextFromPdf($request->file('file'));
            $extractedText = $this->extractTextFromRawContent($request->file('file'));

            if (!$extractedText || strlen($extractedText) < 50) {
                Log::warning("Text extraction failed or was too short.");
                $extractedText = "The system could not extract text from this document. It might be an image-only scan without OCR text layer.";
            } else {
                Log::info("Text extraction successful. Length: " . strlen($extractedText));
            }

            // 4. Azure OpenAI: Classify & Summarize
            $this->classifyAndSummarize($document, $extractedText);

            return redirect()->route('documents.show', $document->id)
                ->with('success', 'Document uploaded and analyzed!');

        } catch (\Exception $e) {
            Log::error("CRITICAL UPLOAD ERROR: " . $e->getMessage());
            return back()->with('error', 'Upload failed. Check laravel.log for details.');
        }
    }

    /**
     * Logic to handle Azure's Async Polling
     */
    private function extractTextFromPdf($file)
    {
        $endpoint = config('services.azure.document.endpoint');
        $key = config('services.azure.document.key');

        // Correct API URL for Layout Analysis (reads text + tables)
        $url = rtrim($endpoint, '/') . "/documentintelligence/documentModels/prebuilt-read:analyze?api-version=2024-02-29-preview";

        Log::info("Sending to Azure Doc Intelligence: " . $url);

        $fileContent = file_get_contents($file->getRealPath());

        // STEP A: SUBMIT JOB
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $key,
            'Content-Type' => 'application/octet-stream',
        ])->withBody($fileContent, 'application/octet-stream')->post($url);

        if ($response->status() === 202) {
            // ASYNC FLOW (Expected)
            $operationUrl = $response->header('Operation-Location');
            Log::info("Azure Accepted Job (202). Polling URL: " . $operationUrl);

            // Poll for up to 15 seconds (5 attempts x 3 seconds)
            for ($i = 0; $i < 5; $i++) {
                sleep(3); // Wait 3 seconds

                $pollResponse = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $key,
                ])->get($operationUrl);

                $status = $pollResponse->json('status');
                Log::info("Polling Attempt " . ($i + 1) . ": Status = " . $status);

                if ($status === 'succeeded') {
                    $content = $pollResponse->json('analyzeResult.content');
                    return $content;
                }

                if ($status === 'failed') {
                    Log::error("Azure Analysis Failed: " . json_encode($pollResponse->json()));
                    return null;
                }
            }
            Log::error("Azure Analysis Timed Out after 15 seconds.");
            return null;

        } elseif ($response->successful()) {
            // SYNC FLOW (Rare, but possible for tiny files)
            Log::info("Azure returned immediate result (200).");
            return $response->json('analyzeResult.content');
        } else {
            // ERROR
            Log::error("Azure Doc Intelligence Error: " . $response->status() . " - " . $response->body());
            return null;
        }
    }

    /**
     * UPDATED: Robust AI Summarizer with Debug Logging
     */
    private function classifyAndSummarize(Document $doc, $text)
    {
        Log::info("Sending Text to OpenAI for summarization...");

        $endpoint = config('services.azure.openai.endpoint');
        $apiKey = config('services.azure.openai.api_key');
        $deployment = config('services.azure.openai.deployment');
        $apiVersion = config('services.azure.openai.api_version');
        $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

        // Explicit Prompt to ensure Keys match
        $systemMessage = "You are a legal expert. Analyze this document.
        Output ONLY valid JSON with these exact keys:
        {
            \"type\": \"Bill, Policy, Report, or Law\",
            \"summary_plain\": \"A formal executive summary.\",
            \"summary_eli5\": \"A simple explanation for a 5-year-old.\"
        }
        Do not use Markdown formatting.";

        $sampleText = substr($text, 0, 15000);

        $response = Http::withHeaders([
            'api-key' => $apiKey, 'Content-Type' => 'application/json'
        ])->post($url, [
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => "Document Title: {$doc->title}\n\nText Sample:\n" . $sampleText],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        if ($response->successful()) {
            $contentRaw = $response->json('choices.0.message.content');

            // LOG THE RAW RESPONSE TO SEE WHAT IS WRONG
            Log::info("OpenAI Raw Response: " . $contentRaw);

            // Clean Markdown if present (```json ... ```)
            $contentClean = str_replace(['```json', '```'], '', $contentRaw);

            $data = json_decode($contentClean, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON Decode Error: " . json_last_error_msg());
            }

            $doc->update([
                'type' => $data['type'] ?? 'Document',
                'summary_plain' => $data['summary_plain'] ?? 'AI failed to generate summary.',
                'summary_eli5' => $data['summary_eli5'] ?? 'AI failed to generate explanation.',
                'extracted_text' => $text,
            ]);
        } else {
            Log::error("OpenAI Error: " . $response->body());
        }
    }

    /**
     * The Smart Reader View
     */
    public function show(Document $document)
    {
        $document->load('annotations.user');
        return view('documents.show', compact('document'));
    }

    /**
     * Chat with the Document
     */
    public function chat(Request $request, Document $document)
    {
        $request->validate(['question' => 'required|string']);

        if (!$document->extracted_text) {
            return response()->json(['answer' => 'I cannot read this document yet. The text extraction failed or is pending.']);
        }

        $endpoint = config('services.azure.openai.endpoint');
        $apiKey = config('services.azure.openai.api_key');
        $deployment = config('services.azure.openai.deployment');
        $apiVersion = config('services.azure.openai.api_version');
        $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

        $systemMessage = "You are an assistant helping a user read a legal document. Use the provided document text to answer. If the answer isn't there, say so.";

        // RAG-lite: Context injection
        $context = substr($document->extracted_text, 0, 20000);

        $response = Http::withHeaders([
            'api-key' => $apiKey, 'Content-Type' => 'application/json'
        ])->post($url, [
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => "Context:\n$context\n\nQuestion: " . $request->question],
            ],
        ]);

        return response()->json(['answer' => $response->json('choices.0.message.content')]);
    }

    /**
     * Add Annotation
     */
    public function annotate(Request $request, Document $document)
    {
        $request->validate(['note' => 'required|string', 'section' => 'nullable|string']);

        $document->annotations()->create([
            'user_id' => Auth::id(),
            'note' => $request->note,
            'section_reference' => $request->section,
        ]);

        return back()->with('success', 'Note added!');
    }

        /**
     * Force re-analysis of the document
     */
    public function regenerate(Document $document)
    {
        try {
            Log::info("--- MANUAL REGENERATION STARTED FOR DOC ID: {$document->id} ---");

            // 1. Check if we need to re-extract text (OCR)
            if (!$document->extracted_text || strlen($document->extracted_text) < 100) {
                Log::info("Text missing or invalid. Retrying Azure Doc Intelligence...");

                if (!Storage::disk('public')->exists($document->file_path)) {
                    return back()->with('error', 'Original file not found in storage.');
                }
                $fileContent = Storage::disk('public')->get($document->file_path);

                // Call the helper (modified to accept raw content)
                $extractedText = $this->extractTextFromRawContent($fileContent);

                if (!$extractedText) {
                    return back()->with('error', 'OCR Failed again. Please check the PDF file.');
                }

                $document->update(['extracted_text' => $extractedText]);
            } else {
                Log::info("Text exists (" . strlen($document->extracted_text) . " chars). Running AI Summary.");
            }

            // 2. Re-run OpenAI Summarization
            $this->classifyAndSummarize($document, $document->extracted_text);

            return back()->with('success', 'Document re-analyzed successfully!');

        } catch (\Exception $e) {
            Log::error("Regeneration Error: " . $e->getMessage());
            return back()->with('error', 'Regeneration failed. Check logs.');
        }
    }

    /**
     * MODIFIED HELPER: Extracts text from raw file content (from Storage)
     * This replaces the previous logic that required an UploadedFile object.
     */
    private function extractTextFromRawContent($fileContent)
    {
        $endpoint = config('services.azure.document.endpoint');
        $key = config('services.azure.document.key');
        $url = rtrim($endpoint, '/') . "/documentintelligence/documentModels/prebuilt-read:analyze?api-version=2024-02-29-preview";

        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $key,
            'Content-Type' => 'application/octet-stream',
        ])->withBody($fileContent, 'application/octet-stream')->post($url);

        if ($response->status() === 202) {
            $operationUrl = $response->header('Operation-Location');
            // Poll Loop
            for ($i = 0; $i < 8; $i++) { // Increased to 8 attempts (24 seconds)
                sleep(3);
                $poll = Http::withHeaders(['Ocp-Apim-Subscription-Key' => $key])->get($operationUrl);
                if ($poll->json('status') === 'succeeded') {
                    return $poll->json('analyzeResult.content');
                }
            }
            Log::error("Regen OCR Timed Out");
            return null;
        } elseif ($response->successful()) {
            return $response->json('analyzeResult.content');
        }

        Log::error("Regen OCR Error: " . $response->body());
        return null;
    }

    // Ensure your existing 'store' method uses this new 'extractTextFromRawContent' helper
    // or keep the old one but map it correctly.
    // To keep it simple, you can Replace 'extractTextFromPdf' in your STORE method
    // with: $this->extractTextFromRawContent(file_get_contents($request->file('file')->getRealPath()));
}
