@extends('layouts.app')

@section('content')
  <h1 class="text-3xl font-bold mb-6">All Comics</h1>

  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
    @foreach($comics as $comic)
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
        <div class="aspect-[3/4] relative rounded-t-lg overflow-hidden">
          <a href="{{ route('comics.show', $comic) }}" class="block w-full h-full">
            @if($comic->cover_path)
              <img src="{{ $comic->cover_url }}" 
                   alt="{{ $comic->title }}" 
                   class="w-full h-full object-cover"
                   loading="lazy" />
            @else
              <div class="absolute inset-0 w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                <span class="text-gray-400 dark:text-gray-500">No cover</span>
              </div>
            @endif
          </a>
        </div>
        <div class="p-3">
          <a href="{{ route('comics.show', $comic) }}" class="block font-semibold text-gray-900 dark:text-white hover:text-primary truncate">
            {{ $comic->title }}
          </a>
          <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">
            {{ $comic->genre->name ?? 'Uncategorized' }}
          </div>
          <div class="mt-2">
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
