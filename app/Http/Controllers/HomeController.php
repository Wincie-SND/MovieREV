<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\TmdbClient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    /**
     * Show the public dashboard.
     *
     * The catalog is read from TMDB on every request; only the community
     * reviews come from the database. Nothing about a movie is stored locally,
     * so the home page no longer depends on the `movies` table.
     */
    public function index(Request $request, TmdbClient $tmdb): View
    {
        $search = trim((string) $request->query('q', ''));

        // A TMDB outage, a rejected credential or a network blip must not take
        // the landing page down: each section falls back to its placeholders.
        $movies = $this->attempt(fn (): Collection => $search === ''
            ? $tmdb->popular()
            : $tmdb->search($search));

        return view('welcome', [
            'search' => $search,
            'featuredMovie' => $movies->first(),
            'popularMovies' => $movies->take(4),
            'recentlyReleasedMovies' => $this->attempt(fn (): Collection => $tmdb->nowPlaying(5)),
            'recentReviews' => $this->recentReviews($search),
            'movieCount' => $movies->count(),
        ]);
    }

    /**
     * A failed read degrades to an empty collection.
     *
     * @param  callable(): Collection<int, mixed>  $callback
     * @return Collection<int, mixed>
     */
    private function attempt(callable $callback): Collection
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            report($exception);

            return collect();
        }
    }

    /**
     * The reviews come from the snapshot each row keeps (`tmdb_id`, title,
     * poster), because the movie itself is no longer in the database.
     *
     * The database may legitimately be unreachable — the home page is a public
     * landing page and the catalog does not need MySQL — so a failed read
     * degrades to an empty section instead of a 500. The same guard covers a
     * missing `reviews` table on a fresh checkout.
     *
     * @return Collection<int, Review>
     */
    private function recentReviews(string $search): Collection
    {
        return $this->attempt(fn (): Collection => Review::query()
            ->with('user')
            ->when($search !== '', fn ($query) => $query->search($search))
            ->orderByDesc('id')
            ->limit(4)
            ->get());
    }
}
