<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

/**
 * A review belongs to the member who wrote it.
 *
 * The controller calls `Gate::authorize()` before touching a review, so an id
 * swapped in the URL is refused server-side rather than merely hidden from the
 * interface. Auto-discovered for {@see Review} by Laravel's naming convention.
 */
class ReviewPolicy
{
    /**
     * Only the author may change a review.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * Only the author may remove a review.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}
