<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Comic;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\Genre;

class SyncFromAzureStorage extends Command
{
    protected $signature = 'az:sync-from-storage 
        {--dry-run : Show what would be created without making changes}';

    protected $description = 'Sync database records from Azure Blob Storage content structure';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $disk = Storage::disk('azure');

        if (!$disk->exists('comics')) {
            $this->error("No 'comics' directory found in Azure storage");
            return 1;
        }

        // List all directories in comics/
        $comicDirs = collect($disk->directories('comics'));
        $this->info("Found " . $comicDirs->count() . " comic directories");

        // Get or create default genre
        $defaultGenre = Genre::firstOrCreate(
            ['slug' => 'uncategorized'],
            ['name' => 'Uncategorized']
        );

        foreach ($comicDirs as $comicPath) {
            $comicSlug = basename($comicPath);
            $this->info("\nProcessing comic: {$comicSlug}");

            // Clean the slug and create a title
            $title = Str::title(str_replace('-', ' ', $comicSlug));

            // Find or create comic
            $comic = Comic::firstOrCreate(
                ['slug' => $comicSlug],
                [
                    'title' => $title,
                    'genre_id' => $defaultGenre->id,
                ]
            );

            if ($comic->wasRecentlyCreated) {
                $this->info("Created new comic: {$title}");
            } else {
                $this->info("Found existing comic: {$title}");
            }

            // Check for cover
            $coverPath = "images/covers/{$comicSlug}/cover";
            $possibleExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $coverFound = false;

            foreach ($possibleExtensions as $ext) {
                if ($disk->exists("{$coverPath}.{$ext}")) {
                    $fullCoverPath = "{$coverPath}.{$ext}";
                    if (!$isDryRun && $comic->cover_path !== $fullCoverPath) {
                        $comic->update(['cover_path' => $fullCoverPath]);
                    }
                    $coverFound = true;
                    $this->info("Updated cover path: {$fullCoverPath}");
                    break;
                }
            }

            if (!$coverFound) {
                $this->warn("No cover found for {$title}");
            }

            // Process chapters
            $chapterDirs = collect($disk->directories($comicPath))
                ->filter(fn($dir) => Str::contains(basename($dir), 'chapter'))
                ->sortBy(function($dir) {
                    preg_match('/chapter(\d+)/', basename($dir), $matches);
                    return $matches[1] ?? 0;
                });

            foreach ($chapterDirs as $chapterDir) {
                preg_match('/chapter(\d+)/', basename($chapterDir), $matches);
                $chapterNumber = $matches[1] ?? null;

                if (!$chapterNumber) {
                    $this->warn("Could not determine chapter number from: " . basename($chapterDir));
                    continue;
                }

                // Find or create chapter
                $chapter = Chapter::firstOrCreate(
                    [
                        'comic_id' => $comic->id,
                        'number' => $chapterNumber
                    ],
                    [
                        'title' => "Chapter {$chapterNumber}",
                        'published_at' => now(),
                    ]
                );

                if ($chapter->wasRecentlyCreated) {
                    $this->info("Created chapter {$chapterNumber}");
                } else {
                    $this->info("Found existing chapter {$chapterNumber}");
                }

                // Process pages
                $pages = collect($disk->files($chapterDir))
                    ->filter(fn($file) => Str::contains(basename($file), 'page'))
                    ->sortBy(function($file) {
                        preg_match('/page(\d+)/', basename($file), $matches);
                        return $matches[1] ?? 0;
                    });

                foreach ($pages as $pagePath) {
                    preg_match('/page(\d+)/', basename($pagePath), $matches);
                    $pageNumber = $matches[1] ?? null;

                    if (!$pageNumber) {
                        $this->warn("Could not determine page number from: " . basename($pagePath));
                        continue;
                    }

                    if (!$isDryRun) {
                        $page = Page::firstOrCreate(
                            [
                                'chapter_id' => $chapter->id,
                                'page_number' => $pageNumber
                            ],
                            [
                                'image_path' => $pagePath
                            ]
                        );

                        if ($page->wasRecentlyCreated) {
                            $this->info("Created page {$pageNumber}");
                        } else if ($page->image_path !== $pagePath) {
                            $page->update(['image_path' => $pagePath]);
                            $this->info("Updated page {$pageNumber} path");
                        }
                    } else {
                        $this->info("[DRY RUN] Would process page {$pageNumber}: {$pagePath}");
                    }
                }
            }
        }

        $this->info("\nSync completed successfully!");
        if ($isDryRun) {
            $this->info("This was a dry run - no changes were made to the database.");
        }

        return 0;
    }
}