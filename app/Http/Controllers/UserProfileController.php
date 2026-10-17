<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(User $user): View
    {
        $recentReviews = Review::query()
            ->where('user_id', $user->id)
            ->withLikeState(auth()->id())
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $favoriteFilmCount = DB::table('lists')
            ->join('list_movies', 'lists.id', '=', 'list_movies.list_id')
            ->where('lists.user_id', $user->id)
            ->where('lists.name', 'Favorites')
            ->distinct()
            ->count('list_movies.tmdb_id');

        $isFollowing = auth()->user()?->following()
            ->where('users.id', $user->id)
            ->exists() ?? false;

        return view('users.show', [
            'profile' => $user,
            'recentReviews' => $recentReviews,
            'favoriteFilmCount' => $favoriteFilmCount,
            'reviewCount' => $user->reviews()->count(),
            'followerCount' => $user->followers()->count(),
            'followingCount' => $user->following()->count(),
            'isFollowing' => $isFollowing,
            'isOwnProfile' => auth()->id() === $user->id,
        ]);
    }
}
