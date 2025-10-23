<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\Rating;
use App\Http\Requests\StoreRatingRequest;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    /**
     * RatingController constructor.
     */
    public function store(StoreRatingRequest $request, Comic $comic)
    {
        $userId = auth()->id();
        $rating = Rating::updateOrCreate(
            ['user_id' => $userId, 'comic_id' => $comic->id],
            ['rating' => $request->rating]
        );

        if ($request->ajax()) {
            $averageRating = Rating::where('comic_id', $comic->id)
                ->avg('rating');

            return response()->json([
                'message' => 'Rating saved successfully',
                'average' => round($averageRating, 1),
                'userRating' => $rating->rating
            ]);
        }

        return back()->with('success', 'Rating saved successfully');
    }

    public function getAverageRating(Comic $comic): JsonResponse
    {
        $averageRating = Rating::where('comic_id', $comic->id)
            ->avg('rating');

        $userRating = null;
        if (auth()->check()) {
            $userRating = Rating::where('user_id', auth()->id())
                ->where('comic_id', $comic->id)
                ->value('rating');
        }

        return response()->json([
            'average' => round($averageRating, 1),
            'userRating' => $userRating,
            'totalRatings' => Rating::where('comic_id', $comic->id)->count()
        ]);
    }
}
