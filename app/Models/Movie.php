<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'release_year', 'genre', 'poster_url'])]
class Movie extends Model
{
    /**
     * The table persists only created_at.
     */
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
        ];
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
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
            $query->where('title', 'like', $like)
                ->orWhere('genre', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /**
     * Return only an HTTP(S) URL or site-relative poster path.
     */
    public function posterSource(): ?string
    {
        $source = trim((string) $this->poster_url);

        if ($source === '' || preg_match('/[\s<>"]/', $source)) {
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
