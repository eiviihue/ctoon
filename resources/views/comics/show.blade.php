@extends('layouts.app')

@section('content')
<div class="comic-detail bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <div class="flex flex-col md:flex-row p-4 md:p-6 lg:p-8 gap-6">
        <div class="w-full md:w-64 lg:w-80 flex-shrink-0">
            <div class="relative pt-[140%] rounded-lg overflow-hidden shadow-lg">
                @if($comic->cover_path)
                    <img src="{{ $comic->cover_url }}" alt="{{ $comic->title }}" 
                         class="absolute inset-0 w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <span class="text-gray-400 dark:text-gray-500">No cover</span>
                    </div>
                @endif
            </div>
            
            @auth
                <form action="{{ route('bookmarks.toggle', $comic) }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg transition duration-200 shadow-sm hover:shadow flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                        </svg>
                        {{ auth()->user()->bookmarks->contains($comic) ? 'Remove Bookmark' : 'Add Bookmark' }}
                    </button>
                </form>
            @endauth
        </div>

        <div class="flex-1">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-4 text-gray-900 dark:text-white">{{ $comic->title }}</h1>
            
            @if($comic->genre)
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary/10 text-primary">
                    {{ $comic->genre->name }}
                </span>
            </div>
            @endif

            <div class="mb-4">
                <x-rating :comic="$comic" size="lg" />
            </div>

            <p class="text-gray-600 dark:text-gray-400 mb-8 text-base leading-relaxed">{{ $comic->description }}</p>

            @if($comic->chapters->count())
                <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Chapters</h2>
                    <div class="space-y-2 chapter-list">
                        @foreach($comic->chapters->sortByDesc('number') as $chapter)
                            <div class="chapter-item">
                                <a href="{{ route('chapters.show', [$comic, $chapter]) }}" 
                                   class="flex justify-between items-center p-4 bg-white dark:bg-gray-800 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-200 group">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg font-medium text-gray-900 dark:text-white group-hover:text-primary">
                                            Chapter {{ $chapter->number }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $chapter->created_at->format('M d, Y') }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 group-hover:text-primary" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center p-8 bg-gray-50 dark:bg-gray-900 rounded-xl">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <p class="text-lg font-medium">No chapters available yet.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($comic->comments->count() || auth()->check())
    <div class="mt-8 border-t border-gray-200 dark:border-gray-800 pt-8">
        <h2 class="text-xl font-semibold mb-4">Comments</h2>
        
        @auth
            <form action="{{ route('comments.store', $comic) }}" method="POST" class="mb-6">
                @csrf
                <textarea name="content" rows="3" placeholder="Leave a comment..." class="input w-full resize-y mb-3"></textarea>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
        @endauth

        <div class="space-y-4">
            @foreach($comic->comments->sortByDesc('created_at') as $comment)
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                    <div class="flex justify-between mb-2">
                        <span class="font-medium">{{ $comment->user->name }}</span>
                        <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300">{{ $comment->content }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection