<?php

namespace App\Console\Commands;

use App\Models\Comic;
use App\Models\Chapter;
use App\Models\Page;
use App\Models\Genre;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportLocalComics extends Command
{
    protected $signature = 'comics:import-local';
    protected $description = 'Import comics from local storage/app/public/comics directory';

    public function handle()
    {
        $this->info('Starting local comics import...');
        
        // Ensure default genre exists
        $genre = Genre::firstOrCreate(
            ['slug' => 'manga'],
            ['name' => 'Manga']
        );

        $localDisk = Storage::disk('public');
        $comicsPath = 'comics';

        if (!$localDisk->exists($comicsPath)) {
            $this->error("Comics directory not found in storage/app/public/comics");
            return 1;
        }

        $comicDirectories = collect($localDisk->directories($comicsPath));
        
        $this->info("Found " . $comicDirectories->count() . " comics");
        
        foreach ($comicDirectories as $comicPath) {
            $comicName = basename($comicPath);
            $this->info("Processing comic: {$comicName}");

            // Create or update comic
            $comic = Comic::firstOrCreate(
                ['slug' => Str::slug($comicName)],
                [
                    'title' => Str::title(str_replace('-', ' ', $comicName)),
                    'genre_id' => $genre->id
                ]
            );

            // Process chapters
            $chapterDirs = collect($localDisk->directories($comicPath))
                ->filter(fn($dir) => Str::startsWith(basename($dir), 'chapter-'));

            foreach ($chapterDirs as $chapterPath) {
                $chapterNumber = (int) Str::after(basename($chapterPath), 'chapter-');
                $this->info("  Processing chapter {$chapterNumber}");

                $chapter = Chapter::firstOrCreate(
                    [
                        'comic_id' => $comic->id,
                        'number' => $chapterNumber
                    ],
                    ['title' => "Chapter {$chapterNumber}"]
                );

                // Process pages
                $pages = collect($localDisk->files($chapterPath))
                    ->filter(fn($file) => Str::startsWith(basename($file), 'page-'))
                    ->sort();

                foreach ($pages as $pagePath) {
                    $pageNumber = (int) Str::between(basename($pagePath), 'page-', '.');
                    $extension = pathinfo($pagePath, PATHINFO_EXTENSION);

                    Page::firstOrCreate(
                        [
                            'chapter_id' => $chapter->id,
                            'number' => $pageNumber
                        ],
                        [
                            'file_path' => $pagePath,
                            'extension' => $extension
                        ]
                    );
                }

                $this->info("    Added " . $pages->count() . " pages");
            }
        }

        $this->info('Import completed successfully!');
    }
}