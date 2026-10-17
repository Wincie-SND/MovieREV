<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\TmdbClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class MovieController extends Controller
{
    /**
     * Show one film from the TMDB catalog.
     *
     * TMDB is asked for the details, credits, certification and similar films
     * in one round trip. Its ids are the page's only key: there is no local
     * `movies` table to fall back on.
     *
     * The reviews shown are MovieREV's own, read from the `reviews` table; no
     * TMDB review endpoint is involved.
     */
    public function show(Request $request, int $movie, TmdbClient $tmdb): View
    {
        try {
            $detail = $tmdb->find($movie);
        } catch (Throwable $exception) {
            // The id may well be valid, so a TMDB outage is not a 404.
            report($exception);

            abort(503, 'The TMDB catalog is unavailable right now.');
        }

        if ($detail === null) {
            abort(404, 'That film is not in the TMDB catalog.');
        }

        $reviews = $this->reviews($movie);
        $user = $request->user();

        $myReview = $user === null
            ? null
            : $reviews->firstWhere('user_id', $user->id);

        $savedLists = [
            'favorites' => false,
            'rate-later' => false,
        ];

        if ($user !== null) {
            $savedNames = DB::table('lists')
                ->join('list_movies', 'lists.id', '=', 'list_movies.list_id')
                ->where('lists.user_id', $user->id)
                ->whereIn('lists.name', ['Favorites', 'Rate Later'])
                ->where('list_movies.tmdb_id', $movie)
                ->pluck('lists.name');

            $savedLists = [
                'favorites' => $savedNames->contains('Favorites'),
                'rate-later' => $savedNames->contains('Rate Later'),
            ];
        }

        // `?edit={id}` refills the form with the member's own review. An id
        // that is not theirs is ignored rather than trusted.
        $editingReview = null;

        if ($user !== null && ($edit = $request->integer('edit')) > 0) {
            $candidate = $reviews->firstWhere('id', $edit);

            $editingReview = $candidate !== null && $candidate->user_id === $user->id
                ? $candidate
                : null;
        }

        return view('movies.show', [
            'detail' => $detail,
            'reviews' => $reviews,
            // The community score is built from member ratings only, so it is
            // null (not TMDB's vote_average) when nobody has reviewed the film.
            'communityRating' => $reviews->isEmpty() ? null : round((float) $reviews->avg('rating'), 1),
            'myReview' => $myReview,
            'editingReview' => $editingReview,
            'savedLists' => $savedLists,
        ]);
    }

    /**
     * This film's community reviews.
     *
     * Like the dashboard, this section must not depend on MySQL being up: a
     * refused connection or a missing table shows "no reviews yet" instead of
     * failing the page.
     *
     * @return Collection<int, Review>
     */
    private function reviews(int $tmdbId): Collection
    {
        try {
            return Review::query()
                ->with('user')
                ->where('tmdb_id', $tmdbId)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();
        } catch (Throwable $exception) {
            report($exception);

            return collect();
        }
    }
}
