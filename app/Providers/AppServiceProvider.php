<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter; // <-- 1. IMPORT LARAVEL'S ADAPTER
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http; // <-- THIS IS THE MISSING LINE
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
use League\Flysystem\Filesystem;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The incorrect Http::macro has been removed.
        // This is the only code that should be in the boot method for now.
        try {
            Storage::extend('azure', function ($app, $config) {
                $client = BlobRestProxy::createBlobService($config['connection_string']);
                $adapter = new AzureBlobStorageAdapter($client, $config['container']);
                $filesystem = new Filesystem($adapter);

                return new FilesystemAdapter($filesystem, $adapter, $config);
            });
        } catch (\Exception $e) {
            Log::error('Could not register Azure Storage driver: ' . $e->getMessage());
        }
    }
}
