<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewLikeController extends Controller
{
    public function store(Request $request, Review $review): RedirectResponse
    {
        abort_if(
            $request->user()->id === (int) $review->user_id,
            403,
            'You cannot like your own review.',
        );

        $request->user()->likedReviews()->syncWithoutDetaching([$review->id]);

        return back();
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_if(
            $request->user()->id === (int) $review->user_id,
            403,
            'You cannot like your own review.',
        );

        $request->user()->likedReviews()->detach($review->id);

        return back();
    }
}
