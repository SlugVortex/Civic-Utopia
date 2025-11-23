<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter; // <-- 1. IMPORT LARAVEL'S ADAPTER
use Illuminate\Support\Facades\Log;
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
        try {
            Storage::extend('azure', function ($app, $config) {
                $client = BlobRestProxy::createBlobService($config['connection_string']);
                $adapter = new AzureBlobStorageAdapter($client, $config['container']);
                $filesystem = new Filesystem($adapter);

                // 2. WRAP THE CORE FILESYSTEM IN LARAVEL'S ADAPTER
                return new FilesystemAdapter($filesystem, $adapter, $config);
            });
        } catch (\Exception $e) {
            Log::error('Could not register Azure Storage driver: ' . $e->getMessage());
        }
    }
}
