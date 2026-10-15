<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Read-only wrapper around the TMDB v3 API.
 *
 * The dashboard and the movie detail page are the only consumers: they ask for
 * lists or for a single film and get back {@see TmdbMovie} /
 * {@see TmdbMovieDetail} value objects. Nothing here touches the database.
 *
 * Credentials come from `config('services.tmdb')`. With neither the v3 API key
 * nor a v4 access token configured, every list method returns an empty
 * collection so the page falls back to its placeholder slots instead of
 * failing, and {@see find()} reports "no such movie".
 */
class TmdbClient
{
    /** Genre names are identical for every list response and change rarely. */
    private const GENRE_CACHE_KEY = 'tmdb.movie-genres';

    private const GENRE_CACHE_SECONDS = 86400;

    private const POSTER_SIZE = 'w500';

    private const BACKDROP_SIZE = 'w1280';

    private const PROFILE_SIZE = 'w185';

    /**
     * The most popular movies right now.
     *
     * @return Collection<int, TmdbMovie>
     */
    public function popular(int $limit = 8): Collection
    {
        return $this->movies('/movie/popular', [], $limit);
    }

    /**
     * Films currently in theatres, which the dashboard shows as "recently
     * released".
     *
     * @return Collection<int, TmdbMovie>
     */
    public function nowPlaying(int $limit = 8): Collection
    {
        return $this->movies('/movie/now_playing', [], $limit);
    }

    /**
     * Movies matching a free-text query.
     *
     * @return Collection<int, TmdbMovie>
     */
    public function search(string $term, int $limit = 8): Collection
    {
        $term = trim($term);

        return $term === ''
            ? collect()
            : $this->movies('/search/movie', ['query' => $term], $limit);
    }

    /**
     * One film with everything the detail page renders: details, credits,
     * certifications and similar films, in a single round trip.
     *
     * Returns null when TMDB says the id does not exist, and throws when the
     * request itself fails, so a caller can tell "no such movie" (404) apart
     * from "TMDB is unreachable" (503).
     */
    public function find(int $id): ?TmdbMovieDetail
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $path = "/movie/{$id}";
        $response = $this->send($path, ['append_to_response' => 'credits,release_dates,similar']);

        if ($response->status() === 404) {
            return null;
        }

        if ($response->failed()) {
            throw new RuntimeException("TMDB responded {$response->status()} to {$path}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || ! isset($payload['id'])) {
            return null;
        }

        // `/movie/{id}` nests genres as objects rather than the `genre_ids`
        // list responses carry, so the full list comes from the payload.
        $genres = $this->detailGenreNames($payload);

        return new TmdbMovieDetail(
            movie: TmdbMovie::fromTmdb(
                $payload,
                $genres === [] ? null : implode(', ', array_slice($genres, 0, 2)),
                $this->imageUrl($payload['poster_path'] ?? null),
            ),
            genres: $genres,
            similar: $this->collection($payload['similar']['results'] ?? [], 5),
            runtime: (int) ($payload['runtime'] ?? 0) > 0 ? (int) $payload['runtime'] : null,
            rated: $this->certification($payload['release_dates']['results'] ?? []),
            tagline: filled($payload['tagline'] ?? null) ? (string) $payload['tagline'] : null,
            director: $this->director($payload['credits']['crew'] ?? []),
            backdrop_url: $this->imageUrl($payload['backdrop_path'] ?? null, self::BACKDROP_SIZE),
            cast: $this->cast($payload['credits']['cast'] ?? []),
        );
    }

    public function isConfigured(): bool
    {
        return filled(config('services.tmdb.key')) || filled(config('services.tmdb.token'));
    }

    /**
     * Absolute URL for a TMDB image path such as `/x8q2am.jpg`.
     */
    public function imageUrl(?string $path, string $size = self::POSTER_SIZE): ?string
    {
        $path = trim((string) $path);

        if ($path === '' || preg_match('/[\s<>"]/', $path) === 1 || str_contains($path, '..')) {
            return null;
        }

        return sprintf(
            '%s/%s/%s',
            rtrim((string) config('services.tmdb.image_base_url'), '/'),
            $size,
            ltrim($path, '/'),
        );
    }

    /**
     * @return Collection<int, TmdbMovie>
     */
    private function movies(string $path, array $query, int $limit): Collection
    {
        if (! $this->isConfigured()) {
            return collect();
        }

        return $this->collection($this->rows($path, $query, 'results'), $limit);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows  entries from a list response
     * @return Collection<int, TmdbMovie>
     */
    private function collection(array $rows, int $limit): Collection
    {
        $genres = $this->genres();

        return collect($rows)
            ->map(fn (array $row): TmdbMovie => TmdbMovie::fromTmdb(
                $row,
                $this->genreLabel($row['genre_ids'] ?? [], $genres),
                $this->imageUrl($row['poster_path'] ?? null),
            ))
            ->take($limit)
            ->values();
    }

    /**
     * @param  'results'|'genres'  $key  the list key the endpoint wraps its rows in
     * @return array<int, array<string, mixed>>
     */
    private function rows(string $path, array $query, string $key): array
    {
        $rows = $this->payload($path, $query)[$key] ?? null;

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $path, array $query = []): array
    {
        $response = $this->send($path, $query);

        if ($response->failed()) {
            throw new RuntimeException("TMDB responded {$response->status()} to {$path}.");
        }

        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }

    private function send(string $path, array $query = []): Response
    {
        return $this->request()->get($path, $query + $this->credentials());
    }

    /**
     * Genre ids arrive as bare integers, so they are resolved against
     * `/genre/movie/list` once a day.
     *
     * @return array<int, string>
     */
    private function genres(): array
    {
        try {
            return Cache::remember(self::GENRE_CACHE_KEY, self::GENRE_CACHE_SECONDS, function (): array {
                $genres = [];

                foreach ($this->rows('/genre/movie/list', [], 'genres') as $genre) {
                    if (isset($genre['id'], $genre['name'])) {
                        $genres[(int) $genre['id']] = (string) $genre['name'];
                    }
                }

                return $genres;
            });
        } catch (Throwable $exception) {
            // Genres are decoration: a card renders fine without its genre.
            report($exception);

            return [];
        }
    }

    /**
     * Every genre of a `/movie/{id}` payload, in TMDB order.
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    private function detailGenreNames(array $payload): array
    {
        $genres = $payload['genres'] ?? [];

        if (! is_array($genres) || $genres === []) {
            // Should not happen, but the id list is a workable fallback.
            return $this->genreNames($payload['genre_ids'] ?? [], $this->genres());
        }

        $names = [];

        foreach ($genres as $genre) {
            if (isset($genre['name'])) {
                $names[] = (string) $genre['name'];
            }
        }

        return $names;
    }

    /**
     * @param  array<int, int|string>  $ids
     * @param  array<int, string>  $names
     * @return array<int, string>
     */
    private function genreNames(array $ids, array $names): array
    {
        $labels = [];

        foreach ($ids as $id) {
            if (isset($names[(int) $id])) {
                $labels[] = $names[(int) $id];
            }
        }

        return $labels;
    }

    /**
     * @param  array<int, int|string>  $ids
     * @param  array<int, string>  $names
     */
    private function genreLabel(array $ids, array $names): ?string
    {
        $labels = $this->genreNames($ids, $names);

        // Two labels keep the card's metadata line to a single row.
        return $labels === [] ? null : implode(', ', array_slice($labels, 0, 2));
    }

    /**
     * US certification ("PG-13", "R", ...) out of the nested release-dates
     * payload; the design's "Rated" row.
     *
     * @param  array<int, array<string, mixed>>  $results
     */
    private function certification(array $results): ?string
    {
        foreach ($results as $country) {
            if (($country['iso_3166_1'] ?? null) !== 'US') {
                continue;
            }

            foreach ($country['release_dates'] ?? [] as $release) {
                $certification = trim((string) ($release['certification'] ?? ''));

                if ($certification !== '') {
                    return $certification;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $crew
     */
    private function director(array $crew): ?string
    {
        $directors = [];

        foreach ($crew as $member) {
            if (($member['job'] ?? null) === 'Director' && filled($member['name'] ?? null)) {
                $directors[] = (string) $member['name'];
            }
        }

        // Co-directed films list more than one; join them the way a byline does.
        return $directors === [] ? null : implode(', ', array_unique($directors));
    }

    /**
     * The top-billed few, in TMDB order. Only what a cast card renders is kept.
     *
     * @param  array<int, array<string, mixed>>  $cast
     * @return array<int, array{name: string, character: string|null, photo_url: string|null}>
     */
    private function cast(array $cast, int $limit = 8): array
    {
        $people = [];

        foreach (array_slice($cast, 0, $limit) as $person) {
            if (! filled($person['name'] ?? null)) {
                continue;
            }

            $character = trim((string) ($person['character'] ?? ''));

            $people[] = [
                'name' => (string) $person['name'],
                'character' => $character === '' ? null : $character,
                'photo_url' => $this->imageUrl($person['profile_path'] ?? null, self::PROFILE_SIZE),
            ];
        }

        return $people;
    }

    private function request(): PendingRequest
    {
        $request = Http::acceptJson()
            ->baseUrl(rtrim((string) config('services.tmdb.base_url'), '/'))
            ->timeout((int) config('services.tmdb.timeout', 10));

        $token = config('services.tmdb.token');

        return filled($token) ? $request->withToken((string) $token) : $request;
    }

    /**
     * A v4 read access token travels as a bearer header, in which case the v3
     * `api_key` parameter must not be sent as well.
     *
     * @return array<string, string>
     */
    private function credentials(): array
    {
        return filled(config('services.tmdb.token'))
            ? []
            : ['api_key' => (string) config('services.tmdb.key')];
    }
}
