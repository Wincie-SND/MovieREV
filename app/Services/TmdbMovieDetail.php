<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Everything the movie detail page needs, for one movie.
 *
 * Wraps a {@see TmdbMovie} rather than repeating its fields, and adds the data
 * only `/movie/{id}` returns: runtime, certification, tagline, director, the
 * billing block and the similar-films strip.
 *
 * The client builds this — it is a plain value object, so it holds no HTTP or
 * database knowledge of its own.
 */
final readonly class TmdbMovieDetail
{
    /**
     * @param  array<int, string>  $genres  every genre name, in TMDB order
     * @param  Collection<int, TmdbMovie>  $similar
     * @param  array<int, array{name: string, character: string|null, photo_url: string|null}>  $cast
     */
    public function __construct(
        public TmdbMovie $movie,
        public array $genres,
        public Collection $similar,
        public ?int $runtime = null,
        public ?string $rated = null,
        public ?string $tagline = null,
        public ?string $director = null,
        public ?string $backdrop_url = null,
        public array $cast = [],
    ) {}

    /**
     * `126` -> `2h 06m`. The minutes are zero-padded to match the design.
     */
    public function runtimeLabel(): ?string
    {
        if ($this->runtime === null || $this->runtime <= 0) {
            return null;
        }

        return sprintf('%dh %02dm', intdiv($this->runtime, 60), $this->runtime % 60);
    }

    public function backdropSource(): ?string
    {
        return TmdbMovie::safeImageUrl($this->backdrop_url);
    }

    public function hasCast(): bool
    {
        return $this->cast !== [];
    }
}
