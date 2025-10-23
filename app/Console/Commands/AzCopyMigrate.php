<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Comic;
use App\Models\Chapter;
use App\Models\Page;

class AzCopyMigrate extends Command
{
    protected $signature = 'storage:azcopy-migrate 
        {--dry-run : Show what would be copied without actually copying}
        {--sas-token= : SAS token for Azure container}';

    protected $description = 'Migrate storage content to Azure using AzCopy';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $sasToken = $this->option('sas-token');

        if (!$sasToken) {
            $this->error('Please provide a SAS token for the Azure container');
            return 1;
        }

        // Get Azure storage settings
        $storageAccount = config('filesystems.disks.azure.name');
        $container = config('filesystems.disks.azure.container');

        if (!$storageAccount || !$container) {
            $this->error('Azure storage configuration is missing');
            return 1;
        }

        $sourceDir = storage_path('app/public');
        $destUrl = "https://{$storageAccount}.blob.core.windows.net/{$container}";

        // Ensure AzCopy is available
        $azCopyPath = $this->findAzCopy();
        if (!$azCopyPath) {
            $this->error('AzCopy not found. Please install AzCopy and ensure it\'s in the system PATH');
            return 1;
        }

        $this->info('Starting migration using AzCopy...');

        // Migrate covers first
        $this->info('Migrating covers...');
        $comics = Comic::whereNotNull('cover_path')->get();
        foreach ($comics as $comic) {
            $sourcePath = storage_path('app/public/' . $comic->cover_path);
            if (!file_exists($sourcePath)) {
                $this->warn("Cover not found for comic {$comic->title}: {$sourcePath}");
                continue;
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $newPath = "images/covers/{$comic->slug}/cover.{$extension}";
            
            if (!$isDryRun) {
                $this->copyFileWithAzCopy(
                    $sourcePath,
                    "{$destUrl}/{$newPath}?{$sasToken}",
                    $azCopyPath
                );

                // Update database record with new path
                $comic->update(['cover_path' => $newPath]);
            }

            $this->info("Migrated cover for {$comic->title}");
        }

        // Migrate chapter pages
        $this->info('Migrating chapter pages...');
        $pages = Page::with(['chapter.comic'])->get();
        foreach ($pages as $page) {
            if (!$page->chapter?->comic) {
                continue;
            }

            $sourcePath = storage_path('app/public/' . $page->image_path);
            if (!file_exists($sourcePath)) {
                $this->warn("Page not found: {$sourcePath}");
                continue;
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $newPath = "comics/{$page->chapter->comic->slug}/chapter{$page->chapter->number}/page{$page->page_number}.{$extension}";

            if (!$isDryRun) {
                $this->copyFileWithAzCopy(
                    $sourcePath,
                    "{$destUrl}/{$newPath}?{$sasToken}",
                    $azCopyPath
                );

                // Update database record
                $page->update(['image_path' => $newPath]);
            }

            $this->info("Migrated page {$page->page_number} of chapter {$page->chapter->number} for {$page->chapter->comic->title}");
        }

        // Bulk copy entire storage directory (for any other files)
        $this->info('Copying remaining files...');
        if (!$isDryRun) {
            $this->copyWithAzCopy(
                $sourceDir,
                "{$destUrl}?{$sasToken}",
                $azCopyPath
            );
        }

        $this->info('Migration completed successfully!');
        if ($isDryRun) {
            $this->info('This was a dry run. No files were actually copied.');
        }

        return 0;
    }

    protected function findAzCopy()
    {
        // Check common locations for AzCopy
        $locations = [
            'azcopy',
            'azcopy.exe',
            'C:\\Program Files\\Azure\\azcopy.exe',
            'C:\\Program Files (x86)\\Azure\\azcopy.exe',
        ];

        foreach ($locations as $location) {
            $result = shell_exec("where {$location} 2>&1");
            if ($result && !str_contains($result, 'Could not find')) {
                return trim($result);
            }
        }

        return null;
    }

    protected function copyWithAzCopy($source, $destination, $azcopyPath)
    {
        $command = "\"{$azcopyPath}\" copy \"{$source}\" \"{$destination}\" --recursive=true";
        $this->info("Running: {$command}");
        passthru($command);
    }

    protected function copyFileWithAzCopy($source, $destination, $azcopyPath)
    {
        $command = "\"{$azcopyPath}\" copy \"{$source}\" \"{$destination}\"";
        $this->info("Running: {$command}");
        passthru($command);
    }
}