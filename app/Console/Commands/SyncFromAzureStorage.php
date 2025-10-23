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
        $disk = Storage::disk('azure');

        if (!$disk->exists('comics')) {
            $this->error("No 'comics' directory found in Azure storage");
            return 1;
        }

        $comicDirs = collect($disk->directories('comics'));
        $this->info("Found " . $comicDirs->count() . " comic directories");

        $defaultGenre = Genre::firstOrCreate(['slug' => 'uncategorized'], ['name' => 'Uncategorized']);

        if (!$isDryRun) {
            if ($this->confirm('This will DELETE all comics, chapters, pages, covers and genres and re-import. Proceed?')) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                if (DB::getSchemaBuilder()->hasTable('covers')) {
                    DB::table('covers')->truncate();
                }
                Page::truncate();
                Chapter::truncate();
                Comic::truncate();
                Genre::truncate();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $this->info('Cleared existing data.');
                $defaultGenre = Genre::firstOrCreate(['slug' => 'uncategorized'], ['name' => 'Uncategorized']);
            } else {
                $this->info('Operation cancelled.');
                return 1;
            }
        }

        foreach ($comicDirs as $comicPath) {
            $comicSlug = basename($comicPath);
            $this->info("\nProcessing comic: {$comicSlug}");

            $title = Str::title(str_replace('-', ' ', $comicSlug));

            if (!$isDryRun) {
                $comic = Comic::create([
                    'title' => $title,
                    'slug' => $comicSlug,
                    'genre_id' => $defaultGenre->id,
                ]);
            } else {
                $comic = new Comic();
                $comic->id = $comicDirs->search($comicPath) + 1;
                $comic->slug = $comicSlug;
                $comic->title = $title;
            }

            $this->info(($isDryRun ? '[DRY] ' : '') . "Comic: {$comic->title} (slug: {$comic->slug})");

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
                        if (!$isDryRun) {
                            if (DB::getSchemaBuilder()->hasTable('covers')) {
                                DB::table('covers')->insert([
                                    'id' => $comic->id,
                                    'comic_id' => $comic->id,
                                    'path' => $coverPath,
                                    'filename' => $filename,
                                    'size' => null,
                                    'disk' => config('filesystems.default', 'azure'),
                                    'is_primary' => true,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                ]);
                            }
                            $comic->update(['cover_path' => $coverPath]);
                        }
                        $coverFound = true;
                        $this->info(($isDryRun ? '[DRY] ' : '') . "Cover: {$coverPath}");
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

                if (!$isDryRun) {
                    $chapter = Chapter::create([
                        'comic_id' => $comic->id,
                        'number' => (int) $chapterNumber,
                        'title' => "Chapter {$chapterNumber}",
                        'published_at' => now(),
                    ]);
                } else {
                    $chapter = new Chapter();
                    $chapter->id = 0;
                    $chapter->number = $chapterNumber;
                }

                $this->info(($isDryRun ? '[DRY] ' : '') . "Chapter: {$chapterNumber}");

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

                    if (!$isDryRun) {
                        $page = Page::create([
                            'chapter_id' => $chapter->id,
                            'page_number' => (int) $pageNumber,
                            'image_path' => $relativePagePath,
                        ]);
                    }

                    $this->info(($isDryRun ? '[DRY] ' : '') . "Page: {$pageNumber} => {$relativePagePath}");
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
