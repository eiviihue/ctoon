@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="display-5 mb-4">My Bookmarks</h1>

    @if($bookmarks->count())
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            @foreach($bookmarks as $bookmark)
                <div class="col">
                    <div class="card h-100">
                        <a href="{{ route('comics.show', $bookmark->comic) }}" class="text-decoration-none">
                            @if($bookmark->comic->cover_path)
                                <img src="{{ $bookmark->comic->cover_url }}" 
                                     alt="{{ $bookmark->comic->title }}"
                                     class="card-img-top"
                                     style="height: 300px; object-fit: cover;"
                                     loading="lazy">
                            @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 300px;">
                                    <span class="text-muted">No cover</span>
                                </div>
                            @endif
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="{{ route('comics.show', $bookmark->comic) }}" class="text-dark text-decoration-none">
                                    {{ $bookmark->comic->title }}
                                </a>
                            </h5>
                            <p class="card-text">
                                <span class="badge bg-secondary">
                                    {{ $bookmark->comic->genre->name ?? 'Uncategorized' }}
                                </span>
                            </p>
                            <form action="{{ route('bookmarks.destroy', $bookmark->comic) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-bookmark me-2"></i>
                                    Remove Bookmark
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <div class="display-1 text-muted mb-4">
                <i class="fas fa-bookmark"></i>
            </div>
            <h3 class="h4 mb-3">No bookmarks yet</h3>
            <p class="text-muted mb-4">Start exploring comics and bookmark your favorites!</p>
            <a href="{{ route('comics.index') }}" class="btn btn-primary">
                Browse Comics
            </a>
        </div>
    @endif
</div>
@endsection