<?php

namespace App\Http\Controllers;

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
        // We'll add logic here later to list uploaded documents.
        // For now, it just shows the view.
        return view('admin.documents.index');
    }

    /**
     * Store an uploaded document in Azure Blob Storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the request
        $request->validate([
            'document' => [
                'required',
                'file',
                'mimes:pdf', // Only allow PDF files
                'max:20480', // Set a max size, e.g., 20MB
            ],
        ]);

        try {
            // 2. Get the uploaded file
            $file = $request->file('document');
            $originalFilename = $file->getClientOriginalName();

            Log::info('[CivicUtopia] PDF upload received.', ['filename' => $originalFilename]);

            // 3. Use Laravel's Storage facade to upload to the 'azure' disk
            // We are streaming the file directly to Azure to be memory-efficient.
            $path = Storage::disk('azure')->putFileAs('', $file, $originalFilename);

            // The 'putFileAs' method returns the path (which is just the filename in this case)
            if ($path) {
                Log::info('[CivicUtopia] Successfully uploaded file to Azure Blob Storage.', [
                    'container' => env('AZURE_STORAGE_CONTAINER'),
                    'path' => $path,
                ]);

                // We will trigger the AI processing job here in the next step.
                // For now, just return a success message.

                return back()->with('status', "Successfully uploaded '{$originalFilename}' to Azure Storage.");
            } else {
                throw new \Exception('Storage::putFileAs returned a falsy value.');
            }

        } catch (\Exception $e) {
            Log::error('[CivicUtopia] Failed to upload file to Azure Blob Storage.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Full trace for debugging
            ]);

            return back()->with('error', 'There was a critical error during the file upload. Please check the logs.');
        }
    }
}
