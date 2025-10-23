<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Cover;
use App\Models\Comic;

class ImportCovers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Options:
     *  --dry-run : don't write to DB, just report
     *  --disk= : storage disk to read from (default: public)
     */
    protected $signature = 'covers:import {--dry-run} {--disk=public}';

    /**
     * The console command description.
     */
    protected $description = 'Import cover files from storage into covers table (supports dry-run)';

    public function handle()
    {
        $dry = $this->option('dry-run');
        $diskName = $this->option('disk') ?: 'public';
        $disk = Storage::disk($diskName);

        $this->info("Scanning disk: $diskName for covers/covers directory...");

        if (! $disk->exists('covers')) {
            $this->warn("No covers directory found on disk '$diskName'. Nothing to import.");
            return 0;
        }

        // use allFiles to recursively include files in subdirectories (covers/comicname/..)
        $files = $disk->allFiles('covers');
        if (empty($files)) {
            $this->warn('No files found under covers/.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $bar->advance();
            $basename = basename($file);
            $size = $disk->size($file);

            // Try to infer comic from the parent folder name: covers/{comicSlug}/file.jpg
            $comic = null;
            $parentDir = basename(dirname($file));
            if ($parentDir && $parentDir !== 'covers') {
                // parentDir is likely the comic slug or name
                $comic = Comic::where('slug', $parentDir)->first();
                if (! $comic) {
                    // try matching by title-ish (slugified)
                    $maybeSlug = Str::slug($parentDir);
                    $comic = Comic::where('slug', 'like', "$maybeSlug%")->first();
                }
            }

            // Fallbacks: try to find by filename id or slug
            if (! $comic) {
                if (preg_match('/^(\d+)[-_]/', $basename, $m)) {
                    $comic = Comic::find((int) $m[1]);
                }
            }
            if (! $comic) {
                $maybeSlug = Str::before($basename, '_');
                $comic = Comic::where('slug', $maybeSlug)->first();
            }

            if (! $comic) {
                // try to find by title-ish name from filename
                $slug = Str::slug(Str::before($basename, '.'));
                $comic = Comic::where('slug', 'like', "$slug%")->first();
            }

            if (! $comic) {
                $this->line("\nSkipping $basename — no matching comic found.");
                continue;
            }

            // Check existing record
            $exists = Cover::where('comic_id', $comic->id)->where('path', $file)->exists();
            if ($exists) {
                continue;
            }

            $data = [
                'comic_id' => $comic->id,
                'path' => $file,
                'filename' => $basename,
                'size' => $size,
                'disk' => $diskName,
                'is_primary' => true,
            ];

            if ($dry) {
                $this->line("\nDry-run: would insert cover for comic {$comic->id} -> $file");
                continue;
            }

            Cover::create($data);
        }

        $bar->finish();
        $this->info('\nImport complete.');
        return 0;
    }
}
