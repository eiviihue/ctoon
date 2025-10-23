<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Requests\StoreComicRequest;
use Illuminate\Support\Facades\Storage;

class ComicController extends Controller
{
    public function index(Request $request)
    {
        $query = Comic::with('genre');
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('genre')) {
            $query->where('genre_id', $request->genre);
        }
        $comics = $query->paginate(12);
        return view('comics.index', compact('comics'));
    }

    public function create()
    {
        $genres = Genre::all();
        return view('comics.create', compact('genres'));
    }

    public function store(StoreComicRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }
        Comic::create($data);
        return redirect()->route('comics.index')->with('success', 'Comic added');
    }

    public function show(Comic $comic)
    {
        $comic->load(['chapters.pages', 'comments.user', 'genre', 'ratings']);
        return view('comics.show', compact('comic'));
    }

    public function edit(Comic $comic)
    {
        $genres = Genre::all();
        return view('comics.edit', compact('comic', 'genres'));
    }

    public function update(StoreComicRequest $request, Comic $comic)
    {
        $data = $request->validated();
        if ($request->hasFile('cover')) {
            if ($comic->cover_path)
                Storage::disk('public')->delete($comic->cover_path);
            $data['cover_path'] = $request->file('cover')->store('covers', 'public');
        }
        $comic->update($data);
        return redirect()->route('comics.show', $comic)->with('success', 'Comic updated');
    }

    public function destroy(Comic $comic)
    {
        $comic->delete();
        return redirect()->route('comics.index')->with('success', 'Comic deleted');
    }
}

