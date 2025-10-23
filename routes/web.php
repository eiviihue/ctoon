<?php

use App\Http\Controllers\{
    ComicController,
    ChapterController,
    BookmarkController,
    CommentController,
    RatingController,
    ProfileController,
    GenreController,
    AuthController
};

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', [ComicController::class, 'index'])->name('home');
Route::resource('comics', ComicController::class);
Route::get('comics/{comic}/chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
Route::middleware('auth')->group(function () {
    Route::resource('chapters', ChapterController::class)->only(['create', 'store', 'destroy']);
    Route::resource('bookmarks', BookmarkController::class)->only(['index', 'store', 'destroy']);
    Route::post('comics/{comic}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('comics/{comic}/rating', [RatingController::class, 'store'])->name('ratings.store');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
});

