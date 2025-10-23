<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comic;

class BookmarkController extends Controller
{
    public function index()
    {
        $bookmarks = auth()->user()->bookmarkedComics()->paginate(12);
        return view('bookmarks.index', compact('bookmarks'));
    }

    public function store(Comic $comic)
    {
        $user = auth()->user();
        $user->bookmarks()->firstOrCreate(['comic_id' => $comic->id]);
        return back()->with('success', 'Bookmarked');
    }

    public function destroy(Comic $comic)
    {
        auth()->user()->bookmarks()->where('comic_id', $comic->id)->delete();
        return back()->with('success', 'Bookmark removed');
    }
}

