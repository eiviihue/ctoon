<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Comic;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\Genre;
use Carbon\Carbon;

class SyncFromAzureStorage extends Command
{
    protected $signature = 'az:sync-from-storage {--dry-run : Show what would be created without making changes}';

    protected $description = 'Sync database records from Azure Blob Storage content structure';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        // Use the application's default disk (usually `public`) instead of Azure-specific disk
        $disk = Storage::disk(config('filesystems.default', 'public'));

        if (!$disk->exists('comics')) {
            $this->error("No 'comics' directory found in Azure storage");
            return 1;
        }

        $comicDirs = collect($disk->directories('comics'));
        $this->info("Found " . $comicDirs->count() . " comic directories");

        $defaultGenre = Genre::firstOrCreate(['slug' => 'uncategorized'], ['name' => 'Uncategorized']);

        foreach ($comicDirs as $comicPath) {
            $comicSlug = basename($comicPath);
            $this->info("\nProcessing comic: {$comicSlug}");

            $title = Str::title(str_replace('-', ' ', $comicSlug));

            // Find or create comic
            $comic = Comic::firstOrNew(['slug' => $comicSlug]);
            if ($comic->exists) {
                $this->info("Found existing comic: {$comic->title} (slug: {$comic->slug})");
            } else {
                if (!$isDryRun) {
                    $comic->fill([
                        'title' => $title,
                        'slug' => $comicSlug,
                        'genre_id' => $defaultGenre->id,
                    ])->save();
                    $this->info("Created new comic: {$title} (slug: {$comicSlug})");
                }
            }

            // Cover handling
            $coverDir = "covers/{$comicSlug}";
            $coverFound = false;
            $validExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if ($disk->exists($coverDir)) {
                $coverFiles = $disk->files($coverDir);
                foreach ($coverFiles as $cf) {
                    $filename = basename($cf);
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (in_array($ext, $validExt)) {
                        $coverPath = "covers/{$comicSlug}/{$filename}";
                        
                        // Check if cover path has changed
                        if ($comic->cover_path !== $coverPath) {
                            if (!$isDryRun) {
                                if (DB::getSchemaBuilder()->hasTable('covers')) {
                                    // Update or insert cover
                                    DB::table('covers')->updateOrInsert(
                                        ['comic_id' => $comic->id],
                                        [
                                            'id' => $comic->id,
                                            'path' => $coverPath,
                                            'filename' => $filename,
                                            'size' => null,
                                            'disk' => config('filesystems.default', 'public'),
                                            'is_primary' => true,
                                            'updated_at' => Carbon::now(),
                                        ]
                                    );
                                }
                                $comic->update(['cover_path' => $coverPath]);
                                $this->info("Updated cover path for {$comic->title}: {$coverPath}");
                            } else {
                                $this->info("[DRY] Would update cover path for {$comic->title}: {$coverPath}");
                            }
                        } else {
                            $this->info("Cover path unchanged for {$comic->title}");
                        }
                        
                        $coverFound = true;
                        break;
                    }
                }
            }

            if (!$coverFound) {
                $this->warn("No cover found for {$title}");
            }

            // Chapters
            $chapterDirs = collect($disk->directories("comics/{$comicSlug}"));
            $chapterDirs = $chapterDirs->filter(function ($d) {
                return preg_match('/chapter(\d+)/i', basename($d));
            })->sortBy(function ($d) {
                preg_match('/chapter(\d+)/i', basename($d), $m);
                return (int) ($m[1] ?? 0);
            });

            foreach ($chapterDirs as $chapterDir) {
                preg_match('/chapter(\d+)/i', basename($chapterDir), $m);
                $chapterNumber = $m[1] ?? null;
                if (!$chapterNumber) {
                    $this->warn('Could not determine chapter number for: ' . basename($chapterDir));
                    continue;
                }

                // Find or create chapter
                $chapter = Chapter::firstOrNew([
                    'comic_id' => $comic->id,
                    'number' => (int) $chapterNumber
                ]);

                if ($chapter->exists) {
                    $this->info("Found existing chapter {$chapterNumber} for {$comic->title}");
                } else if (!$isDryRun) {
                    $chapter->fill([
                        'title' => "Chapter {$chapterNumber}",
                        'published_at' => now(),
                    ])->save();
                    $this->info("Created new chapter {$chapterNumber} for {$comic->title}");
                }

                // Pages: support filenames like page1.jpg, page_01.jpg, 1.jpg, 001.jpg
                $pageFiles = collect($disk->files($chapterDir))->filter(function ($f) {
                    $base = basename($f);
                    // possible patterns: page123, page_123, 123, 00123
                    return preg_match('/(?:page[_-]?)(\d+)/i', $base) || preg_match('/^(\d+)\./', $base);
                })->sortBy(function ($f) {
                    $base = basename($f);
                    if (preg_match('/(?:page[_-]?)(\d+)/i', $base, $m)) {
                        return (int) $m[1];
                    }
                    if (preg_match('/^(\d+)\./', $base, $m)) {
                        return (int) $m[1];
                    }
                    return 0;
                });

                foreach ($pageFiles as $pf) {
                    $base = basename($pf);
                    $pageNumber = null;
                    if (preg_match('/(?:page[_-]?)(\d+)/i', $base, $m)) {
                        $pageNumber = (int) $m[1];
                    } elseif (preg_match('/^(\d+)\./', $base, $m)) {
                        $pageNumber = (int) $m[1];
                    }

                    if (!$pageNumber) {
                        $this->warn('Could not determine page number for: ' . $base);
                        continue;
                    }

                    $ext = strtolower(pathinfo($pf, PATHINFO_EXTENSION)) ?: 'jpg';
                    $relativePagePath = "comics/{$comicSlug}/chapter{$chapterNumber}/{$pageNumber}.{$ext}";

                    // Find or create page
                    $page = Page::firstOrNew([
                        'chapter_id' => $chapter->id,
                        'page_number' => (int) $pageNumber
                    ]);

                    // Only update if path has changed
                    if ($page->image_path !== $relativePagePath) {
                        if (!$isDryRun) {
                            $page->image_path = $relativePagePath;
                            $page->save();
                            $this->info("Updated page {$pageNumber} path in chapter {$chapterNumber}: {$relativePagePath}");
                        } else {
                            $this->info("[DRY] Would update page {$pageNumber} path: {$relativePagePath}");
                        }
                    } else {
                        $this->info("Page {$pageNumber} path unchanged in chapter {$chapterNumber}");
                    }
                }
            }
        }

        $this->info("\nSync completed successfully!");
        if ($isDryRun) {
            $this->info('This was a dry run - no changes were made to the database.');
        }

        return 0;
    }
}
