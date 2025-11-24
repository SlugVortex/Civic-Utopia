<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filename;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("[ProcessDocumentJob] Starting to process document: {$this->filename}");

        try {
            // === PART 1: SCAN THE DOCUMENT WITH AZURE DOCUMENT INTELLIGENCE ===
            if (!Storage::disk('azure')->exists($this->filename)) {
                throw new \Exception("File does not exist in Azure Storage: {$this->filename}");
            }
            $fileContent = Storage::disk('azure')->get($this->filename);
            Log::info("[ProcessDocumentJob] Downloaded file content from Azure Storage.");

            $docIntelEndpoint = env('AZURE_AI_DOCUMENT_INTELLIGENCE_ENDPOINT');
            $docIntelApiKey = env('AZURE_AI_DOCUMENT_INTELLIGENCE_API_KEY');

            $analyzeUrl = "{$docIntelEndpoint}documentintelligence/documentModels/prebuilt-layout:analyze?api-version=2023-10-31-preview";

            $analysisResponse = Http::withHeaders([
                'Content-Type' => 'application/pdf',
                'Ocp-Apim-Subscription-Key' => $docIntelApiKey,
            ])
            ->withBody($fileContent, 'application/pdf')
            ->post($analyzeUrl);

            if ($analysisResponse->failed()) {
                Log::error("[ProcessDocumentJob] Document Intelligence analysis request failed.", [
                    'status' => $analysisResponse->status(),
                    'response' => $analysisResponse->body()
                ]);
                $analysisResponse->throw();
            }

            $resultUrl = $analysisResponse->header('Operation-Location');
            if (!$resultUrl) {
                throw new \Exception('Could not get Operation-Location header from Document Intelligence response.');
            }

            $status = '';
            $fullContent = '';

            while ($status !== 'succeeded') {
                sleep(3); // Wait 3 seconds between checks
                $resultResponse = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $docIntelApiKey,
                ])->get($resultUrl);

                if (!$resultResponse->ok()) {
                     throw new \Exception('Failed to poll for Document Intelligence result.');
                }

                $status = $resultResponse->json('status');
                Log::info("[ProcessDocumentJob] Polling Document Intelligence. Status: {$status}");

                if ($status === 'failed') {
                    throw new \Exception('Document Intelligence analysis failed. Check Azure portal for details.');
                }
                if ($status === 'succeeded') {
                    $fullContent = $resultResponse->json('analyzeResult.content');
                }
            }
            Log::info("[ProcessDocumentJob] Successfully extracted content from PDF. Total length: " . strlen($fullContent) . " chars.");


            // === PART 2: INDEX THE CONTENT WITH AZURE AI SEARCH ===

            $searchEndpoint = env('AZURE_AI_SEARCH_ENDPOINT');
            $searchApiKey = env('AZURE_AI_SEARCH_API_KEY');
            $searchIndexName = env('AZURE_AI_SEARCH_INDEX_NAME');

            // We are now sending the sourcefile and uploadDate again.
            $documentsToIndex = [
                'value' => [
                    [
                        '@search.action' => 'upload',
                        'id' => base64_encode($this->filename),
                        'content' => $fullContent,
                        'sourcefile' => $this->filename,
                        'uploadDate' => now()->toIso8601String(),
                    ]
                ]
            ];

            $indexResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'api-key' => $searchApiKey,
            ])->post("{$searchEndpoint}/indexes/{$searchIndexName}/docs/index?api-version=2021-04-30-Preview", $documentsToIndex);

            if ($indexResponse->failed()) {
                Log::error("[ProcessDocumentJob] AI Search indexing request failed.", [
                    'status' => $indexResponse->status(),
                    'response' => $indexResponse->body()
                ]);
                $indexResponse->throw();
            }

            Log::info("[ProcessDocumentJob] Successfully indexed document '{$this->filename}' in Azure AI Search.");

        } catch (\Exception $e) {
            Log::error("[ProcessDocumentJob] FAILED to process document: {$this->filename}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
