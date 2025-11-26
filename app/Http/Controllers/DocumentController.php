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

            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception("File storage failed.");
            }

            Log::info("File stored at: " . $path);

            // 2. Create Initial Record (Draft Mode)
            $document = Document::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'file_path' => $path,
                'country' => $request->country,
                'type' => 'Processing...',
                'is_public' => false,
            ]);

            // 3. Azure Document Intelligence (Extract Text)
            $fileContent = Storage::disk('public')->get($path);
            $extractedText = $this->extractTextFromRawContent($fileContent);

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
            return back()->with('error', 'Upload failed. Check logs.');
        }
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
     * Helper: Extracts text using Azure Document Intelligence
     */
    private function extractTextFromRawContent($fileContent)
    {
        $endpoint = config('services.azure.document.endpoint');
        $key = config('services.azure.document.key');

        // Using 'prebuilt-read' model for best OCR results
        $url = rtrim($endpoint, '/') . "/documentintelligence/documentModels/prebuilt-read:analyze?api-version=2024-02-29-preview";

        try {
            $response = Http::timeout(60)->withHeaders([
                'Ocp-Apim-Subscription-Key' => $key,
                'Content-Type' => 'application/octet-stream',
            ])->withBody($fileContent, 'application/octet-stream')->post($url);

            if ($response->status() === 202) {
                // ASYNC FLOW: Poll for results
                $operationUrl = $response->header('Operation-Location');

                for ($i = 0; $i < 10; $i++) { // Poll for 30 seconds (10 x 3s)
                    sleep(3);
                    $pollResponse = Http::withHeaders(['Ocp-Apim-Subscription-Key' => $key])->get($operationUrl);
                    $status = $pollResponse->json('status');

                    if ($status === 'succeeded') {
                        return $pollResponse->json('analyzeResult.content');
                    }
                    if ($status === 'failed') {
                        Log::error("Azure Analysis Status: FAILED");
                        return null;
                    }
                }
                Log::error("Regen OCR Timed Out");
                return null;
            } elseif ($response->successful()) {
                // SYNC FLOW (Rare)
                return $response->json('analyzeResult.content');
            }

            Log::error("Regen OCR Error: " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("Azure Doc Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper: Summarize with OpenAI (Robust JSON handling)
     */
    private function classifyAndSummarize(Document $doc, $text)
    {
        Log::info("Sending Text to OpenAI for summarization...");

        $endpoint = config('services.azure.openai.endpoint');
        $apiKey = config('services.azure.openai.api_key');
        $deployment = config('services.azure.openai.deployment');
        $apiVersion = config('services.azure.openai.api_version');
        $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

        // Explicit Prompt to ensure Keys match and JSON is valid
        $systemMessage = "You are a legal expert. Analyze this document.
        Output ONLY valid JSON with these exact keys:
        {
            \"type\": \"Bill, Policy, Report, or Law\",
            \"summary_plain\": \"A formal executive summary.\",
            \"summary_eli5\": \"A simple explanation for a 5-year-old.\"
        }
        Do not use Markdown formatting.";

        $sampleText = substr($text, 0, 15000); // Truncate to save tokens

        $response = Http::timeout(45)->withHeaders([
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

            // FIX: Strip Markdown backticks if present (Common GPT-4 issue)
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
     * Toggle Public/Private
     */
    public function togglePublic(Document $document)
    {
        $document->is_public = !$document->is_public;
        $document->save();

        $status = $document->is_public ? 'Published! It is now visible in the Legal Library.' : 'Unpublished. It is now a private draft.';
        return back()->with('success', $status);
    }

    /**
     * Translate Document Summaries
     */
    public function translate(Request $request, Document $document)
    {
        $request->validate(['language' => 'required|string']);
        $lang = $request->language;

        if ($lang === 'English') {
            return response()->json([
                'summary_plain' => $document->summary_plain,
                'summary_eli5' => $document->summary_eli5,
            ]);
        }

        try {
            $endpoint = config('services.azure.openai.endpoint');
            $apiKey = config('services.azure.openai.api_key');
            $deployment = config('services.azure.openai.deployment');
            $apiVersion = config('services.azure.openai.api_version');
            $url = rtrim($endpoint, '/') . "/openai/deployments/{$deployment}/chat/completions?api-version={$apiVersion}";

            $systemMessage = "You are a professional translator. Translate the JSON values into {$lang}. Maintain JSON structure.";

            $payload = [
                'summary_plain' => $document->summary_plain,
                'summary_eli5' => $document->summary_eli5
            ];

            $response = Http::withHeaders([
                'api-key' => $apiKey, 'Content-Type' => 'application/json'
            ])->post($url, [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => json_encode($payload)],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            return response()->json(json_decode($response->json('choices.0.message.content')));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Translation failed'], 500);
        }
    }
}
