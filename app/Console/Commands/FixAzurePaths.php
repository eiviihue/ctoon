<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Page;
use App\Models\Comic;

class FixAzurePaths extends Command
{
    protected $signature = 'azure:fix-paths {--dry-run}';
    protected $description = 'Fix DB image_path and cover_path to match uploaded Azure blob keys (dry-run option)';

    public function handle()
    {
        $dry = $this->option('dry-run');

        $this->info('Scanning pages...');
        $pages = Page::all();
        $updated = 0;
        foreach ($pages as $p) {
            $original = $p->image_path;
            // Normalize: replace 'chapter-1/page-1.jpg' with 'chapter1/1.jpg' if found
            $candidate = preg_replace('#chapter-(\d+)#', 'chapter$1', $original);
            $candidate = preg_replace('#page-(\d+)\.jpg#', '$1.jpg', $candidate);
            if ($candidate !== $original) {
                $this->line("Will update Page {$p->id}: {$original} -> {$candidate}");
                if (!$dry) {
                    $p->image_path = $candidate;
                    $p->save();
                }
                $updated++;
            }
        }

        $this->info("Pages processed: " . count($pages));
        $this->info("Pages updated: {$updated}");

        $this->info('Scanning comics for cover_path...');
        $comics = Comic::all();
        $cUpdated = 0;
        foreach ($comics as $c) {
            if (empty($c->cover_path)) continue;
            $orig = $c->cover_path;
            $cand = preg_replace('#chapter-(\d+)#', 'chapter$1', $orig);
            $cand = preg_replace('#page-(\d+)\.jpg#', '$1.jpg', $cand);
            if ($cand !== $orig) {
                $this->line("Will update Comic {$c->id}: {$orig} -> {$cand}");
                if (!$dry) {
                    $c->cover_path = $cand;
                    $c->save();
                }
                $cUpdated++;
            }
        }
        $this->info("Comics updated: {$cUpdated}");

        return 0;
    }
}
