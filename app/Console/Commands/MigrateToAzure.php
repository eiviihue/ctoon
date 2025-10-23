<?php

namespace App\Console\Commands;

use App\Models\Comic;
use App\Models\Cover;
use App\Models\Page;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateToAzure extends Command
{
    protected $signature = 'storage:migrate-to-azure {--dry-run : Run without making actual changes}';
    protected $description = 'Migrate existing uploads from local storage to Azure';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $this->info($isDryRun ? 'Running in dry-run mode' : 'Starting migration to Azure');

        // Migrate covers first
        $this->migrateCovers($isDryRun);

        // Get all pages from database
        $pages = Page::with(['chapter.comic'])->get();
        $this->info("Found {$pages->count()} pages to migrate");

        $localDisk = Storage::disk('public');
        $azureDisk = Storage::disk('azure');

        $skipped = 0;
        $processed = 0;

        foreach ($pages as $page) {
            if (!$page->chapter || !$page->chapter->comic) {
                $this->warn("Skipping page ID {$page->id}: Missing chapter or comic relation");
                $skipped++;
                continue;
            }

            $chapter = $page->chapter;
            $comic = $chapter->comic;
            
            // Build the source path (current local path)
            $sourcePath = "comics/kimetsunoyaiba/chapter1/" . $page->page_number . ".jpg";
            if (empty($sourcePath)) {
                $this->warn("Skipping page ID {$page->id}: No image path set");
                $skipped++;
                continue;
            }

            if (!$localDisk->exists($sourcePath)) {
                $this->error("Source file not found for page ID {$page->id}: {$sourcePath}");
                $skipped++;
                continue;
            }

            // Get the file extension from the source path
            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);

            // Build the destination path (new Azure path)
            $destinationPath = "comics/{$comic->slug}/chapter-{$chapter->number}/page-{$page->page_number}.{$extension}";

            $this->info("Migrating: {$sourcePath} -> {$destinationPath}");

            if (!$isDryRun) {
                try {
                    // Copy file to Azure
                    $contents = $localDisk->get($sourcePath);
                    if (!$contents) {
                        throw new \Exception("Could not read source file");
                    }
                    
                    $success = $azureDisk->put($destinationPath, $contents);
                    if (!$success) {
                        throw new \Exception("Could not write to Azure");
                    }

                    // Update database record
                    $page->update([
                        'image_path' => $destinationPath,
                        'disk' => 'azure'
                    ]);

                    $this->info("Successfully migrated {$destinationPath}");
                    $processed++;
                } catch (\Exception $e) {
                    $this->error("Failed to migrate {$sourcePath}: " . $e->getMessage());
                    $skipped++;
                }
            } else {
                $processed++;
            }
        }

        $this->info($isDryRun ? 'Dry run completed' : 'Migration completed');
        $this->info("Processed: {$processed}, Skipped: {$skipped}");
    }

    protected function migrateCovers($isDryRun)
    {
        $this->info("\nMigrating covers...");
        $covers = Cover::with('comic')->get();
        $this->info("Found {$covers->count()} covers to migrate");

        $localDisk = Storage::disk('public');
        $azureDisk = Storage::disk('azure');

        $skipped = 0;
        $processed = 0;

        foreach ($covers as $cover) {
            if (!$cover->comic) {
                $this->warn("Skipping cover ID {$cover->id}: Missing comic relation");
                $skipped++;
                continue;
            }

            $sourcePath = $cover->path;
            if (empty($sourcePath)) {
                $this->warn("Skipping cover ID {$cover->id}: No file path set");
                $skipped++;
                continue;
            }

            if (!$localDisk->exists($sourcePath)) {
                $this->error("Source file not found for cover ID {$cover->id}: {$sourcePath}");
                $skipped++;
                continue;
            }

            // Keep the same path structure in Azure
            $destinationPath = $sourcePath;

            $this->info("Migrating cover: {$sourcePath} -> {$destinationPath}");

            if (!$isDryRun) {
                try {
                    // Copy file to Azure
                    $contents = $localDisk->get($sourcePath);
                    if (!$contents) {
                        throw new \Exception("Could not read source file");
                    }
                    
                    $success = $azureDisk->put($destinationPath, $contents);
                    if (!$success) {
                        throw new \Exception("Could not write to Azure");
                    }

                    // Update database record
                    $cover->update([
                        'disk' => 'azure'
                    ]);

                    $this->info("Successfully migrated cover {$destinationPath}");
                    $processed++;
                } catch (\Exception $e) {
                    $this->error("Failed to migrate cover {$sourcePath}: " . $e->getMessage());
                    $skipped++;
                }
            } else {
                $processed++;
            }
        }

        $this->info("\nCover migration completed");
        $this->info("Processed: {$processed}, Skipped: {$skipped}");
    }
}