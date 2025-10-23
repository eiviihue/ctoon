@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="text-2xl font-bold mb-6">My Bookmarks</h1>

    @if($bookmarks->count())
        <div class="comic-grid">
            @foreach($bookmarks as $bookmark)
                <div class="comic-card">
                    <a href="{{ route('comics.show', $bookmark->comic) }}" class="comic-card__cover">
                        @if($bookmark->comic->cover_path)
                            <img src="{{ $bookmark->comic->cover_url }}" 
                                 alt="{{ $bookmark->comic->title }}" 
                                 loading="lazy" />
                        @else
                            <div class="absolute inset-0 w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                <span class="text-gray-400 dark:text-gray-500">No cover</span>
                            </div>
                        @endif
                    </a>
                    <div class="comic-card__info">
                        <a href="{{ route('comics.show', $bookmark->comic) }}" class="comic-title">
                            {{ $bookmark->comic->title }}
                        </a>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $bookmark->comic->genre->name ?? 'Uncategorized' }}
                        </div>
                        <form action="{{ route('bookmarks.toggle', $bookmark->comic) }}" 
                              method="POST" 
                              class="mt-3">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-3 py-1.5 text-sm bg-red-500 hover:bg-red-600 text-white rounded flex items-center justify-center gap-2 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                                </svg>
                                Remove Bookmark
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No bookmarks yet</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Start exploring comics and bookmark your favorites!</p>
            <a href="{{ route('comics.index') }}" class="inline-block mt-4 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition">
                Browse Comics
            </a>
        </div>
    @endif
</div>
@endsection