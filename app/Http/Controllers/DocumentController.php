<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocumentJob; // <-- 1. IMPORT THE JOB
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display the document upload form.
     */
    public function index()
    {
        return view('admin.documents.index');
    }

    /**
     * Store an uploaded document in Azure Blob Storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        try {
            $file = $request->file('document');
            $originalFilename = $file->getClientOriginalName();
            Log::info('[CivicUtopia] PDF upload received.', ['filename' => $originalFilename]);

            $path = Storage::disk('azure')->putFileAs('', $file, $originalFilename);

            if ($path) {
                Log::info('[CivicUtopia] Successfully uploaded file to Azure Blob Storage. Dispatching processing job.', [
                    'container' => env('AZURE_STORAGE_CONTAINER'),
                    'filename' => $originalFilename,
                ]);

                // 2. DISPATCH THE JOB
                // This tells Laravel to process the document in the background.
                ProcessDocumentJob::dispatch($originalFilename);

                return back()->with('status', "Successfully uploaded '{$originalFilename}'. It is now being processed by the AI.");
            } else {
                throw new \Exception('Storage::putFileAs returned a falsy value.');
            }

        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to upload file to Azure Blob Storage.', [
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'There was a critical error during the file upload.');
        }
    }
}
