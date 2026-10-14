<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieController extends Controller
{
    /**
     * List catalog entries for administrators.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $movies = Movie::query()
            ->search($search)
            ->orderByDesc('id')
            ->get();

        return view('admin.movies.index', [
            'movies' => $movies,
            'search' => $search,
        ]);
    }

    /**
     * Show the administrator’s movie-entry form.
     */
    public function create(): View
    {
        return view('admin.movies.create', ['movie' => new Movie]);
    }

    /**
     * Persist an administrator-entered movie.
     */
    public function store(Request $request): RedirectResponse
    {
        $movie = Movie::create($this->validated($request));

        return redirect()
            ->route('admin.movies.index')
            ->with('status', "Added “{$movie->title}” to the catalog.");
    }

    /**
     * Show the administrator’s movie-editing form.
     */
    public function edit(Movie $movie): View
    {
        return view('admin.movies.edit', compact('movie'));
    }

    /**
     * Update an administrator-entered movie.
     */
    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $movie->update($this->validated($request));

        return redirect()
            ->route('admin.movies.index')
            ->with('status', "Updated “{$movie->title}”.");
    }

    /**
     * Remove a movie and its dependent reviews.
     */
    public function destroy(Movie $movie): RedirectResponse
    {
        $title = $movie->title;
        $movie->delete();

        return redirect()
            ->route('admin.movies.index')
            ->with('status', "Removed “{$title}” from the catalog.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'release_year' => ['nullable', 'integer', 'min:1888', 'max:'.((int) date('Y') + 2)],
            'genre' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:5000'],
            'poster_url' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
