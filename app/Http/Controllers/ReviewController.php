<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\TmdbClient;
use App\Services\TmdbMovieDetail;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Throwable;

/**
 * MovieREV's own reviews.
 *
 * Every review here is written by an authenticated member and stored in the
 * local `reviews` table; nothing is read from or written to TMDB's review
 * endpoints. TMDB is asked only for the movie snapshot (`tmdb_id`, title,
 * poster) a review keeps so the dashboard can render without a `movies` table.
 */
class ReviewController extends Controller
{
    /**
     * Store the authenticated member's review of a TMDB movie.
     *
     * The reviewer is always taken from the session — a `user_id` sent by the
     * browser is ignored — and a movie may be reviewed once per member.
     */
    public function store(Request $request, int $movie, TmdbClient $tmdb): RedirectResponse
    {
        $validated = $this->validated($request);

        $user = $request->user();

        if ($this->alreadyReviewed($user->id, $movie)) {
            return $this->duplicateResponse($movie);
        }

        // Snapshot the movie from TMDB so the review can outlive the request
        // without a local `movies` table to join.
        $detail = $this->lookup($movie, $tmdb);

        try {
            Review::create([
                'user_id' => $user->id,
                'tmdb_id' => $movie,
                'movie_title' => $detail->movie->title,
                'poster_url' => $detail->movie->poster_url,
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'body' => $validated['body'],
            ]);
        } catch (QueryException $exception) {
            // Lost a race to the unique index between the check and the insert:
            // the member still gets the "one review per movie" guidance.
            report($exception);

            return $this->duplicateResponse($movie);
        }

        return $this->toMovie($movie)->with('status', 'Thanks! Your review has been posted.');
    }

    /**
     * Update the member's own review.
     */
    public function update(Request $request, Review $review): RedirectResponse
    {
        Gate::authorize('update', $review);

        $validated = $this->validated($request);

        $review->update([
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
        ]);

        return $this->toMovie($review->tmdb_id)->with('status', 'Your review has been updated.');
    }

    /**
     * Delete the member's own review.
     */
    public function destroy(Review $review): RedirectResponse
    {
        Gate::authorize('delete', $review);

        $tmdbId = $review->tmdb_id;

        $review->delete();

        return $this->toMovie($tmdbId)->with('status', 'Your review has been deleted.');
    }

    /**
     * @return array{rating: int, title: string|null, body: string}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'rating.between' => 'Choose a rating between 1 and 5 stars.',
            'body.min' => 'Tell us a little more — at least 10 characters.',
            'body.max' => 'Reviews are capped at 2000 characters.',
            'title.max' => 'Review titles are capped at 120 characters.',
        ]);
    }

    private function alreadyReviewed(int $userId, int $movie): bool
    {
        return Review::query()
            ->where('user_id', $userId)
            ->where('tmdb_id', $movie)
            ->exists();
    }

    private function duplicateResponse(int $movie): RedirectResponse
    {
        return $this->toMovie($movie)
            ->withErrors(['body' => 'You have already reviewed this movie — edit your existing review instead.'])
            ->withInput();
    }

    /**
     * The movie's TMDB details, for the snapshot a review keeps.
     */
    private function lookup(int $movie, TmdbClient $tmdb): TmdbMovieDetail
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

        return $detail;
    }

    private function toMovie(int $movie): RedirectResponse
    {
        return redirect()->to(route('movies.show', $movie).'#reviews');
    }
}
