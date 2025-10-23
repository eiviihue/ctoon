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
        try {
            auth()->user()->bookmarks()->firstOrCreate([
                'comic_id' => $comic->id
            ]);
            
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Bookmarked successfully']);
            }
            
            return back()->with('success', 'Bookmarked successfully');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to bookmark'], 500);
            }
            
            return back()->with('error', 'Failed to bookmark');
        }
    }

    public function destroy(Comic $comic)
    {
        try {
            auth()->user()->bookmarks()
                ->where('comic_id', $comic->id)
                ->delete();
            
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Bookmark removed successfully']);
            }
            
            return back()->with('success', 'Bookmark removed successfully');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to remove bookmark'], 500);
            }
            
            return back()->with('error', 'Failed to remove bookmark');
        }
    }
}

