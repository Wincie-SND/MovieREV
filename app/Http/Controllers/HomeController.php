<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Show the public dashboard backed by admin-entered catalog records.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $moviesTable = Schema::hasTable('movies');
        $reviewsTable = Schema::hasTable('reviews');
        $movieDataAvailable = $moviesTable && $reviewsTable;

        $movies = collect();
        $recentReviews = collect();

        if ($moviesTable) {
            $movieQuery = Movie::query();

            if ($movieDataAvailable) {
                $movieQuery->withAvg('reviews as average_rating', 'rating');
                $movieQuery->withCount('reviews');
            }

            $movies = $movieQuery
                ->search($search)
                ->orderByDesc('id')
                ->limit(8)
                ->get();
        }

        if ($movieDataAvailable) {
            $recentReviews = Review::query()
                ->with(['movie', 'user'])
                ->whereHas('movie')
                ->when($search !== '', function ($query) use ($search): void {
                    $like = '%'.$search.'%';

                    $query->where(function ($query) use ($like): void {
                        $query->where('body', 'like', $like)
                            ->orWhereHas('movie', function ($query) use ($like): void {
                                $query->where('title', 'like', $like)
                                    ->orWhere('genre', 'like', $like)
                                    ->orWhere('description', 'like', $like);
                            });
                    });
                })
                ->orderByDesc('id')
                ->limit(4)
                ->get();
        }

        $featuredMovie = $movies->first();
        $recentMovieIds = $recentReviews
            ->map(fn (Review $review) => $review->movie_id)
            ->filter()
            ->unique()
            ->values();
        $recentlyReviewedMovies = collect();

        if ($moviesTable && $recentMovieIds->isNotEmpty()) {
            $reviewedMovieQuery = Movie::query()->whereIn('id', $recentMovieIds);

            if ($movieDataAvailable) {
                $reviewedMovieQuery->withAvg('reviews as average_rating', 'rating');
                $reviewedMovieQuery->withCount('reviews');
            }

            $recentlyReviewedMovies = $reviewedMovieQuery
                ->get()
                ->sortBy(fn (Movie $movie) => array_search($movie->id, $recentMovieIds->all(), true))
                ->values()
                ->take(5);
        }

        $popularMovies = $movies->take(4);

        return view('welcome', [
            'search' => $search,
            'featuredMovie' => $featuredMovie,
            'popularMovies' => $popularMovies,
            'recentlyReviewedMovies' => $recentlyReviewedMovies,
            'recentReviews' => $recentReviews,
            'movieCount' => $moviesTable ? Movie::count() : 0,
        ]);
    }
}
