@extends('layouts.app')

@section('content')
<div class="comic-detail">
    <div style="display: flex; padding: 2rem; gap: 2rem;">
        <div style="flex-shrink: 0; width: 250px;">
            @if($comic->cover_path)
                <img src="{{ $comic->cover_url }}" alt="{{ $comic->title }}" 
                     style="width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            @else
                <img src="{{ asset('images/placeholder-cover.png') }}" alt="No cover" 
                     style="width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            @endif
            
            @auth
                <form action="{{ route('bookmarks.toggle', $comic) }}" method="POST" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" class="btn" style="width: 100%;">
                        {{ auth()->user()->bookmarks->contains($comic) ? 'Remove Bookmark' : 'Add Bookmark' }}
                    </button>
                </form>
            @endauth
        </div>

        <div style="flex: 1;">
            <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1rem;">{{ $comic->title }}</h1>
            
            @if($comic->genre)
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                    <span style="background: var(--gray-100); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; color: var(--gray-600);">
                        {{ $comic->genre->name }}
                    </span>
            </div>
            @endif

            <p style="color: var(--gray-600); margin-bottom: 2rem;">{{ $comic->description }}</p>

            @if($comic->chapters->count())
                <div style="background: var(--gray-100); border-radius: 0.5rem; padding: 1.5rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Chapters</h2>
                    <div style="display: grid; gap: 0.75rem;">
                        @foreach($comic->chapters->sortByDesc('number') as $chapter)
                            <a href="{{ route('chapters.show', [$comic, $chapter]) }}" 
                               style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: white; border-radius: 0.375rem; text-decoration: none; color: inherit; transition: all 0.2s;">
                                <span>Chapter {{ $chapter->number }}</span>
                                <span style="color: var(--gray-600); font-size: 0.875rem;">{{ $chapter->created_at->format('M d, Y') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <p style="text-align: center; padding: 2rem; color: var(--gray-600); background: var(--gray-100); border-radius: 0.5rem;">
                    No chapters available yet.
                </p>
            @endif
        </div>
    </div>

    @if($comic->comments->count() || auth()->check())
    <div style="border-top: 1px solid var(--gray-200); padding: 2rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Comments</h2>
        
        @auth
            <form action="{{ route('comments.store', $comic) }}" method="POST" style="margin-bottom: 2rem;">
                @csrf
                <textarea name="content" rows="3" placeholder="Leave a comment..." 
                          style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: 0.5rem; margin-bottom: 1rem; resize: vertical;"></textarea>
                <button type="submit" class="btn">Post Comment</button>
            </form>
        @endauth

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($comic->comments->sortByDesc('created_at') as $comment)
                <div style="background: var(--gray-100); padding: 1rem; border-radius: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="font-weight: 500;">{{ $comment->user->name }}</span>
                        <span style="color: var(--gray-600); font-size: 0.875rem;">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p style="color: var(--gray-800);">{{ $comment->content }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection