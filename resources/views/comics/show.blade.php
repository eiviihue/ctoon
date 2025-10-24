@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="position-relative">
                        @if($comic->cover_path)
                            <img src="{{ $comic->cover_url }}" alt="{{ $comic->title }}" class="img-fluid rounded">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="height: 400px;">
                                <span class="text-muted">No cover</span>
                            </div>
                        @endif
                    </div>
                    @auth
                        <form
                            action="{{ auth()->user()->bookmarks()->where('comic_id', $comic->id)->exists() ? route('bookmarks.destroy', $comic) : route('bookmarks.store', $comic) }}"
                            method="POST" class="mt-3 bookmark-form" data-comic-id="{{ $comic->id }}">
                            @csrf
                            @if(auth()->user()->bookmarks()->where('comic_id', $comic->id)->exists())
                                @method('DELETE')
                            @endif
                            <button type="submit"
                                class="btn {{ auth()->user()->bookmarks()->where('comic_id', $comic->id)->exists() ? 'btn-danger' : 'btn-primary' }} w-100 d-flex align-items-center justify-content-center gap-2">
                                <i
                                    class="fas {{ auth()->user()->bookmarks()->where('comic_id', $comic->id)->exists() ? 'fa-bookmark' : 'fa-bookmark' }}"></i>
                                {{ auth()->user()->bookmarks()->where('comic_id', $comic->id)->exists() ? 'Remove Bookmark' : 'Add Bookmark' }}
                            </button>
                        </form>
                    @endauth
                </div>

                <div class="col-12 col-md-8 col-lg-9">
                    <h1 class="display-5 fw-bold mb-3">{{ $comic->title }}</h1>

                    @if($comic->genre)
                        <div class="mb-3">
                            <span class="badge bg-primary">
                                {{ $comic->genre->name }}
                            </span>
                        </div>
                    @endif

                    <div class="mb-3">
                        <div>
                            <strong>Average</strong>
                            <div class="h4 mb-0" id="averageRating">{{ number_format($comic->averageRating(), 1) }}</div>
                            <small class="text-muted" id="ratingCount">{{ $comic->ratings()->count() }} ratings</small>
                        </div>
                    </div>

                    <p class="lead mb-4">{{ $comic->description }}</p>

                    @if($comic->chapters->count())
                        <div class="card">
                            <div class="card-header">
                                <h2 class="h5 mb-0">Chapters</h2>
                            </div>
                            <div class="list-group list-group-flush">
                                @foreach($comic->chapters->sortByDesc('number') as $chapter)
                                    <a href="{{ route('chapters.show', [$comic, $chapter]) }}"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">Chapter {{ $chapter->number }}</h6>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted">
                                                {{ $chapter->created_at->format('M d, Y') }}
                                            </small>
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="card text-center">
                            <div class="card-body py-5">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="h5 text-muted">No chapters available yet.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($comic->comments->count() || auth()->check())
                <div class="mt-5 pt-4 border-top">
                    <h2 class="h4 mb-4">Comments</h2>
                    @auth
                        <form action="{{ route('comments.store', $comic) }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="chapter_id" value="">
                            <div class="mb-3">
                                <textarea name="body" rows="3" class="form-control" placeholder="Leave a comment..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    @endauth
                    <div class="comment-list">
                        @foreach($comic->comments->where('chapter_id', null)->sortByDesc('created_at') as $comment)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="fw-bold">{{ $comment->user->name }}</span>
                                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="card-text">{{ $comment->body }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection