<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\TmdbClient;
use Illuminate\Support\Collection;
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
     */
    public function show(int $movie, TmdbClient $tmdb): View
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

        return view('movies.show', [
            'detail' => $detail,
            'reviews' => $this->reviews($movie),
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
                ->orderByDesc('id')
                ->get();
        } catch (Throwable $exception) {
            report($exception);

            return collect();
        }
    }
}
