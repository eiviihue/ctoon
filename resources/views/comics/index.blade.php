@extends('layouts.app')

@section('content')
  <h1 class="text-3xl font-bold mb-6">All Comics</h1>

  <div class="comic-grid">
    @foreach($comics as $comic)
      <div class="comic-card">
        <a href="{{ route('comics.show', $comic) }}" class="comic-card__cover">
          @if($comic->cover_path)
            <img src="{{ $comic->cover_url }}" alt="{{ $comic->title }}" loading="lazy" />
          @else
            <div class="absolute inset-0 w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
              <span class="text-gray-400 dark:text-gray-500">No cover</span>
            </div>
          @endif
        </a>
        <div class="comic-card__info">
          <a href="{{ route('comics.show', $comic) }}" class="comic-title">
            {{ $comic->title }}
          </a>
          <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ $comic->genre->name ?? 'Uncategorized' }}
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">
    {{ $comics->withQueryString()->links() }}
  </div>
@endsection
