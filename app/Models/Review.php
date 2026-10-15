<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A member's review of a TMDB movie.
 *
 * Movies are not stored locally, so a review keeps its own snapshot of the
 * movie it points at: `tmdb_id` is the link the API understands, while
 * `movie_title` and `poster_url` let the dashboard render a review without a
 * round trip to TMDB (or a `movies` table to join).
 *
 * These are MovieREV's own reviews only — never anything pulled from TMDB.
 */
#[Fillable(['user_id', 'tmdb_id', 'movie_title', 'poster_url', 'rating', 'title', 'body'])]
class Review extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tmdb_id' => 'integer',
            'rating' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        return $query->where(function (Builder $query) use ($like): void {
            $query->where('body', 'like', $like)
                ->orWhere('movie_title', 'like', $like);
        });
    }

    /**
     * Return only an HTTP(S) URL or site-relative poster path.
     */
    public function posterSource(): ?string
    {
        $source = trim((string) $this->poster_url);

        if ($source === '' || preg_match('/[\s<>"]/', $source) === 1) {
            return null;
        }

        if (str_starts_with($source, '/')) {
            return $source;
        }

        if (filter_var($source, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return str_starts_with($source, 'http://') || str_starts_with($source, 'https://')
            ? $source
            : null;
    }
}
