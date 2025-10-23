@extends('layouts.app')

@section('content')
  <h1 class="text-3xl font-bold mb-6">All Comics</h1>

  <div class="comic-grid">
    @foreach($comics as $comic)
      <div class="comic-card" data-category="{{ $comic->genre->slug ?? 'uncategorized' }}">
        <div class="comic-card__cover">
          <a href="{{ route('comics.show', $comic) }}" class="block w-full h-full">
            @if($comic->cover_path)
              <img data-src="{{ $comic->cover_url }}" alt="{{ $comic->title }}" class="lazy" loading="lazy">
            @else
              <div class="absolute inset-0 w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                <span class="text-gray-400 dark:text-gray-500">No cover</span>
              </div>
            @endif
          </a>
        </div>
        <div class="comic-card__info">
          <h3 class="comic-title"><a href="{{ route('comics.show', $comic) }}">{{ $comic->title }}</a></h3>
          <p class="text-sm text-gray-400 mt-1">{{ $comic->genre->name ?? 'Uncategorized' }}</p>
          <div class="mt-3">
            <x-rating :comic="$comic" size="sm" />
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">
    {{ $comics->withQueryString()->links() }}
  </div>
@endsection
