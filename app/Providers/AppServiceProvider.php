<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemAdapter as IlluminateFilesystemAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;
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
        Storage::extend('azure', function ($app, $config) {
            $client = BlobRestProxy::createBlobService(sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
                $config['name'],
                $config['key']
            ));

            $adapter = new AzureBlobStorageAdapter($client, $config['container']);
            $flysystem = new Flysystem($adapter);

            return new IlluminateFilesystemAdapter($flysystem, $adapter, $config);
        });
    }
}
