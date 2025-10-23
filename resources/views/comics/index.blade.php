@extends('layouts.app')

@section('content')
  <h1 class="display-4 mb-4">All Comics</h1>

  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
    @foreach($comics as $comic)
      <div class="col">
        <div class="card h-100" data-category="{{ $comic->genre->slug ?? 'uncategorized' }}">
          <a href="{{ route('comics.show', $comic) }}" class="text-decoration-none">
            @if($comic->cover_path)
              <img src="{{ $comic->cover_url }}" 
                   src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 3 4'%3E%3C/svg%3E"
                   alt="{{ $comic->title }}" 
                   class="card-img-top lazy"
                   onload="this.classList.add('loaded')">
            @else
              <div class="card-img-top d-flex align-items-center justify-content-center">
                <span class="text-muted">No cover</span>
              </div>
            @endif
          </a>
          <div class="px-2">
            <x-rating :comic="$comic" />
          </div>
          <div class="card-body">
            <h5 class="card-title">
              <a href="{{ route('comics.show', $comic) }}" class="text-dark text-decoration-none">{{ $comic->title }}</a>
            </h5>
            <p class="card-text">
              <span class="badge bg-secondary">{{ $comic->genre->name ?? 'Uncategorized' }}</span>
            </p>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4">
    {{ $comics->withQueryString()->links() }}
  </div>
@endsection
