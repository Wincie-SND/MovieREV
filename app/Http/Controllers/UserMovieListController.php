<?php

namespace App\Http\Controllers;

use App\Services\TmdbClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class UserMovieListController extends Controller
{
    /** @var array<string, string> */
    private const LISTS = [
        'favorites' => 'Favorites',
        'rate-later' => 'Rate Later',
    ];

    public function index(Request $request, TmdbClient $tmdb): View
    {
        $savedLists = DB::table('lists')
            ->where('user_id', $request->user()->id)
            ->whereIn('name', array_values(self::LISTS))
            ->get()
            ->keyBy('name');

        $lists = collect(self::LISTS)->map(function (string $name, string $slug) use ($savedLists, $tmdb): array {
            $savedList = $savedLists->get($name);
            $entries = $savedList === null
                ? collect()
                : DB::table('list_movies')
                    ->where('list_id', $savedList->id)
                    ->orderByDesc('added_at')
                    ->get(['tmdb_id', 'added_at']);

            return [
                'name' => $name,
                'slug' => $slug,
                'movies' => $entries->map(function (object $entry) use ($tmdb): array {
                    $movie = null;

                    try {
                        $movie = $tmdb->find((int) $entry->tmdb_id)?->movie;
                    } catch (Throwable $exception) {
                        report($exception);
                    }

                    return [
                        'id' => (int) $entry->tmdb_id,
                        'added_at' => $entry->added_at,
                        'movie' => $movie,
                    ];
                }),
            ];
        });

        return view('users.lists', ['lists' => $lists]);
    }

    public function store(Request $request, int $movie, string $list, TmdbClient $tmdb): RedirectResponse
    {
        abort_if($movie < 1 || $movie > 2147483647, 404);

        $name = $this->listName($list);

        try {
            $detail = $tmdb->find($movie);
        } catch (Throwable $exception) {
            report($exception);

            abort(503, 'The TMDB catalog is unavailable right now.');
        }

        abort_if($detail === null, 404, 'That film is not in the TMDB catalog.');

        $user = $request->user();

        // Locking the owning user serializes creation of the two built-in
        // lists, so simultaneous requests cannot create duplicate lists.
        $listId = DB::transaction(function () use ($user, $name): int {
            DB::table('users')->where('id', $user->id)->lockForUpdate()->first();

            $existingId = DB::table('lists')
                ->where('user_id', $user->id)
                ->where('name', $name)
                ->value('id');

            if ($existingId !== null) {
                return (int) $existingId;
            }

            return (int) DB::table('lists')->insertGetId([
                'user_id' => $user->id,
                'name' => $name,
                'created_at' => now(),
            ]);
        });

        $inserted = DB::table('list_movies')->insertOrIgnore([
            'list_id' => $listId,
            'tmdb_id' => $movie,
        ]);

        $message = $inserted > 0
            ? "Added to {$name}."
            : "This movie is already in {$name}.";

        return redirect()->route('movies.show', $movie)->with('status', $message);
    }

    public function destroy(Request $request, int $movie, string $list): RedirectResponse
    {
        abort_if($movie < 1 || $movie > 2147483647, 404);

        $name = $this->listName($list);
        $validated = $request->validate([
            'return_to' => ['nullable', 'in:movie,lists'],
        ]);

        $savedList = DB::table('lists')
            ->where('user_id', $request->user()->id)
            ->where('name', $name)
            ->first();

        $removed = $savedList === null
            ? 0
            : DB::table('list_movies')
                ->where('list_id', $savedList->id)
                ->where('tmdb_id', $movie)
                ->delete();

        $message = $removed > 0
            ? "Removed from {$name}."
            : "This movie is not in {$name}.";

        $redirect = ($validated['return_to'] ?? 'lists') === 'movie'
            ? redirect()->route('movies.show', $movie)
            : redirect()->route('movie-lists.index');

        return $redirect->with('status', $message);
    }

    private function listName(string $slug): string
    {
        abort_unless(isset(self::LISTS[$slug]), 404);

        return self::LISTS[$slug];
    }
}
