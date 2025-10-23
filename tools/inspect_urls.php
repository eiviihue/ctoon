<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

$comic = App\Models\Comic::with('chapters.pages')->first();
if (!$comic) {
    echo "NO_COMICS\n";
    exit(0);
}

echo "COMIC_ID: {$comic->id}\n";
echo "COVER_PATH: {$comic->cover_path}\n";
echo "COVER_URL: {$comic->cover_url}\n";
$chapter = $comic->chapters->first();
if ($chapter) {
    $page = $chapter->pages->first();
    if ($page) {
        echo "PAGE_PATH: {$page->image_path}\n";
        echo "PAGE_IMAGE_URL (accessor): " . $page->image_url . "\n";
    }
}
