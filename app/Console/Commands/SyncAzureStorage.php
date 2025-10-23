<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Comic;
use App\Models\Chapter;
use App\Models\Page;

class SyncAzureStorage extends Command
{
    protected $signature = 'az:sync-storage {--local-path= : Local path to scan (defaults to storage/app/public)} {--comic= : Only process a single comic folder name} {--dry-run : Show actions without performing uploads or DB writes}';

    protected $description = 'Sync files from storage/app/public to Azure and update Page records (supports dry-run)';

    public function handle()
    {
        $localPath = $this->option('local-path') ?? storage_path('app/public');
        $dryRun = $this->option('dry-run');

        if (!is_dir($localPath)) {
            $this->error("Local path not found: {$localPath}");
            return 1;
        }

        $this->info("Scanning local path: {$localPath}");

        // Expect structure: storage/app/public/{comicTitle}/{chapterFolder}/{pageFiles}
        $comicDirs = array_filter(scandir($localPath), function ($d) use ($localPath) {
            return $d !== '.' && $d !== '..' && is_dir("{$localPath}/{$d}");
        });

        // If specific comic is provided, filter to it
        $filterComic = $this->option('comic');
        if ($filterComic) {
            $comicDirs = array_values(array_filter($comicDirs, function ($d) use ($filterComic) {
                return strcasecmp($d, $filterComic) === 0;
            }));
            if (empty($comicDirs)) {
                $this->error("No folder found for comic '{$filterComic}' in {$localPath}");
                return 1;
            }
        }

        foreach ($comicDirs as $comicDir) {
            $comicTitle = $comicDir;
            $this->info("Processing comic folder: {$comicTitle}");

            // Try matching by slug first, then title. If none found, create with a slug.
            $comicSlugCandidate = Str::slug($comicTitle);
            $comic = Comic::where('slug', $comicSlugCandidate)
                ->orWhere('title', $comicTitle)
                ->first();
            if (!$comic) {
                $this->warn("  Comic not found: '{$comicTitle}'. Will create a new Comic record.");
                if (!$dryRun) {
                    // Ensure we have a default genre to satisfy NOT NULL foreign key
                    $defaultGenre = \App\Models\Genre::firstOrCreate(
                        ['slug' => 'uncategorized'],
                        ['name' => 'Uncategorized']
                    );

                    $comic = Comic::create([
                        'title' => $comicTitle,
                        'slug' => $comicSlugCandidate,
                        'genre_id' => $defaultGenre->id,
                    ]);
                    $this->info("  Created comic id={$comic->id} slug={$comic->slug} genre_id={$defaultGenre->id}");
                }
            }

            $comicPath = rtrim($localPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $comicDir;

            $chapterDirs = array_filter(scandir($comicPath), function ($d) use ($comicPath) {
                return $d !== '.' && $d !== '..' && is_dir("{$comicPath}/{$d}");
            });

            foreach ($chapterDirs as $chapterDir) {
                $this->info("  Found chapter folder: {$chapterDir}");

                // Try to extract chapter number from folder name (e.g., chapter1, chapter-1, 1)
                $chapterNumber = null;
                if (preg_match('/chapter[-_ ]?(\d+)/i', $chapterDir, $m)) {
                    $chapterNumber = (int)$m[1];
                } elseif (preg_match('/^(\d+)$/', $chapterDir, $m)) {
                    $chapterNumber = (int)$m[1];
                } elseif (preg_match('/(\d+)/', $chapterDir, $m)) {
                    $chapterNumber = (int)$m[1];
                }

                $chapter = null;
                if (isset($comic) && isset($comic->id) && $chapterNumber !== null) {
                    $chapter = Chapter::where('comic_id', $comic->id)->where('number', $chapterNumber)->first();
                }

                if (!$chapter) {
                    $this->warn("    Chapter record not found (number: {$chapterNumber}). Will create one.");
                    if (!$dryRun && isset($comic) && isset($comic->id)) {
                        // If chapter number is null, pick next number
                        if ($chapterNumber === null) {
                            $chapterNumber = (int) ($comic->chapters()->max('number') ?? 0) + 1;
                        }
                        $chapter = $comic->chapters()->create(['number' => $chapterNumber, 'title' => $chapterDir]);
                        $this->info("    Created chapter id={$chapter->id} number={$chapter->number}");
                    }
                }

                $chapterPath = $comicPath . DIRECTORY_SEPARATOR . $chapterDir;
                $files = array_filter(scandir($chapterPath), function ($f) use ($chapterPath) {
                    return $f !== '.' && $f !== '..' && is_file($chapterPath . DIRECTORY_SEPARATOR . $f);
                });

                foreach ($files as $file) {
                    $pageNumber = pathinfo($file, PATHINFO_FILENAME);
                    $localFile = $chapterPath . DIRECTORY_SEPARATOR . $file;

                    // Build azure path: comics/{comicId}/chapters/{chapterId}/{filename}
                    // Use slug-based paths: comics/{comic-slug}/chapter-{number}/{filename}
                    $comicSlug = isset($comic) && isset($comic->slug) ? $comic->slug : Str::slug($comicTitle);
                    $chapterNumberForPath = isset($chapter) && isset($chapter->number) ? $chapter->number : ($chapterNumber ?? 'unknown');
                    $azurePath = "comics/{$comicSlug}/chapter-{$chapterNumberForPath}/{$file}";

                    $this->line("    File: {$localFile} -> {$azurePath}");

                    if (!$dryRun) {
                        $stream = fopen($localFile, 'r');
                        Storage::disk('azure')->put($azurePath, $stream);
                        if (is_resource($stream)) fclose($stream);

                        // Update or create Page record
                        if (isset($chapter) && isset($chapter->id)) {
                            $pageModel = Page::where('chapter_id', $chapter->id)->where('page_number', (int)$pageNumber)->first();
                            if ($pageModel) {
                                $pageModel->update(['image_path' => $azurePath]);
                            } else {
                                Page::create(['chapter_id' => $chapter->id, 'image_path' => $azurePath, 'page_number' => (int)$pageNumber]);
                            }
                        }
                    }
                }
            }
        }

        $this->info('Sync finished.');
        return 0;
    }
}
