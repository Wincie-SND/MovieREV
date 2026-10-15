<?php

namespace App\Services;

/**
 * A movie as the dashboard needs it.
 *
 * The catalog lives in TMDB, so there is no local `movies` table and no
 * Eloquent model behind this: it is the immutable shape that
 * `welcome.blade.php` and `<x-movie-card>` operate on.
 *
 * TMDB scores films out of ten while `<x-rating-stars>` and the featured hero
 * are both five-point, so `average_rating` is normalised on the way in.
 */
final readonly class TmdbMovie
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $genre = null,
        public ?string $description = null,
        public ?int $release_year = null,
        public ?string $poster_url = null,
        public float $average_rating = 0.0,
        public int $reviews_count = 0,
    ) {}

    /**
     * @param  array<string, mixed>  $movie  one entry from a TMDB list response
     */
    public static function fromTmdb(array $movie, ?string $genre = null, ?string $posterUrl = null): self
    {
        $overview = trim((string) ($movie['overview'] ?? ''));
        $title = trim((string) ($movie['title'] ?? $movie['name'] ?? ''));
        $votes = (int) ($movie['vote_count'] ?? 0);

        return new self(
            id: (int) ($movie['id'] ?? 0),
            title: $title === '' ? 'Untitled' : $title,
            genre: $genre,
            description: $overview === '' ? null : $overview,
            release_year: self::year((string) ($movie['release_date'] ?? '')),
            poster_url: $posterUrl,
            average_rating: ((float) ($movie['vote_average'] ?? 0)) / 2,
            reviews_count: $votes,
        );
    }

    /**
     * Only an absolute HTTP(S) URL is rendered, so anything else counts as
     * "no image" and `<x-poster-art>` is used instead.
     *
     * TMDB URLs are built by {@see TmdbClient::imageUrl()} and are safe by
     * construction; the check still matters for the poster snapshot stored on
     * a review, which comes straight out of the database.
     */
    public static function safeImageUrl(?string $url): ?string
    {
        $source = trim((string) $url);

        if ($source === '' || preg_match('/[\s<>\"]/', $source) === 1) {
            return null;
        }

        return str_starts_with($source, 'http://') || str_starts_with($source, 'https://')
            ? $source
            : null;
    }

    public function posterSource(): ?string
    {
        return self::safeImageUrl($this->poster_url);
    }

    private static function year(string $releaseDate): ?int
    {
        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', trim($releaseDate), $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
