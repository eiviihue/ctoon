<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\Rating;
use App\Http\Requests\StoreRatingRequest;

class RatingController extends Controller
{
    public function store(StoreRatingRequest $request, Comic $comic)
    {
        $userId = auth()->id();
        Rating::updateOrCreate(
            ['user_id' => $userId, 'comic_id' => $comic->id],
            ['rating' => $request->rating]
        );
        return back()->with('success', 'Rating saved');
    }
}

