<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use App\Services\TmdbClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class FilmsController
{
    public function __invoke(Request $request, TmdbClient $tmdb): View
    {
        abort_unless(Auth::user()->usertype === 'admin', 403);

        $search = trim((string) $request->query('q', ''));
        $genreId = $request->filled('genre') ? (int) $request->query('genre') : null;
        $reviewStatus = in_array($request->query('status'), ['reviewed', 'unreviewed'], true)
            ? (string) $request->query('status')
            : 'all';
        $page = max(1, min(500, (int) $request->query('page', 1)));
        $catalogError = null;

        try {
            $catalog = $tmdb->browse($search, $page, $genreId);
        } catch (Throwable $exception) {
            report($exception);
            $catalog = ['movies' => collect(), 'page' => $page, 'total_pages' => 0, 'total_results' => 0, 'genres' => []];
            $catalogError = 'The movie catalog is temporarily unavailable. Please try again shortly.';
        }

        $reviewCounts = $catalog['movies']->isEmpty()
            ? collect()
            : Review::query()
                ->select('tmdb_id', DB::raw('count(*) as community_reviews'))
                ->whereIn('tmdb_id', $catalog['movies']->pluck('id'))
                ->groupBy('tmdb_id')
                ->pluck('community_reviews', 'tmdb_id');

        $films = $catalog['movies']->map(function ($film) use ($reviewCounts) {
            return [
                'film' => $film,
                'community_reviews' => (int) $reviewCounts->get($film->id, 0),
            ];
        })->filter(function (array $row) use ($reviewStatus): bool {
            return match ($reviewStatus) {
                'reviewed' => $row['community_reviews'] > 0,
                'unreviewed' => $row['community_reviews'] === 0,
                default => true,
            };
        })->values();

        return view('admin.films', [
            'films' => $films,
            'search' => $search,
            'genreId' => $genreId,
            'reviewStatus' => $reviewStatus,
            'genres' => $catalog['genres'],
            'page' => $catalog['page'],
            'totalPages' => $catalog['total_pages'],
            'totalResults' => $catalog['total_results'],
            'catalogError' => $catalogError,
        ]);
    }
}
