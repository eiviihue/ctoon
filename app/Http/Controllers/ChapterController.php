<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Comic;
use App\Models\Page;
use App\Http\Requests\StoreChapterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChapterController extends Controller
{
    public function create(Comic $comic)
    {
        return view('chapters.create', compact('comic'));
    }

    public function store(StoreChapterRequest $request, Comic $comic)
    {
        $data = $request->validated();
        $chapter = $comic->chapters()->create([
            'number' => $data['number'],
            'title' => $data['title'] ?? null,
        ]);

        if ($request->hasFile('pages')) {
            foreach ($request->file('pages') as $i => $file) {
                // Use azure disk so uploaded pages go to your Azure Blob container.
                $filename = $file->getClientOriginalName() ?: ($i + 1) . '.' . $file->extension();
                $comicSlug = $comic->slug ?? \Illuminate\Support\Str::slug($comic->title);
                $chapterNumber = $chapter->number ?? $data['number'] ?? ($i + 1);
                $path = Storage::disk('azure')->putFileAs(
                    "comics/{$comicSlug}/chapter-{$chapterNumber}",
                    $file,
                    $filename
                );

                Page::create([
                    'chapter_id' => $chapter->id,
                    'image_path' => $path,
                    'page_number' => $i + 1,
                ]);
            }
        }

        return redirect()->route('comics.show', $comic)->with('success', 'Chapter uploaded');
    }

    public function show(Comic $comic, Chapter $chapter)
    {
        $chapter->load('pages');
        return view('chapters.show', compact('comic', 'chapter'));
    }

    public function destroy(Comic $comic, Chapter $chapter)
    {
        $chapter->delete();
        return back()->with('success', 'Chapter deleted');
    }
}
