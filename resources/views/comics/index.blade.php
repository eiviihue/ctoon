@extends('layouts.app')

@section('content')
  <h1 class="text-3xl font-bold mb-4">All Comics</h1>

  <div class="grid grid-cols-1 gap-4">
    @foreach($comics as $comic)
      <div class="flex items-center gap-4 border p-3 rounded">
        <a href="{{ route('comics.show', $comic) }}" class="w-24 h-32 block overflow-hidden">
          @if($comic->cover_path)
            <img src="{{ $comic->cover_url }}" alt="{{ $comic->title }}" class="object-cover w-full h-full" />
          @else
            <div class="w-full h-full bg-gray-200 flex items-center justify-center">No cover</div>
          @endif
        </a>
        <div>
          <a href="{{ route('comics.show', $comic) }}" class="text-xl font-semibold">{{ $comic->title }}</a>
          <div class="text-sm text-gray-600">{{ $comic->genre->name ?? 'Uncategorized' }}</div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">
    {{ $comics->withQueryString()->links() }}
  </div>
@endsection
