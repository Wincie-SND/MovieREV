<?php

namespace App\Http\Controllers\Admin;

use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        abort_unless(Auth::user()->usertype === 'admin', 403);

        $monthStarts = collect(range(5, 0))->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo));
        $userGrowth = $monthStarts->map(function (Carbon $month): array {
            $nextMonth = $month->copy()->addMonth();

            return [
                'label' => $month->format('M'),
                'count' => User::query()->where('created_at', '<', $nextMonth)->count(),
            ];
        });

        $weeklyActivity = collect(range(6, 0))->map(function (int $daysAgo): array {
            $day = now()->startOfDay()->subDays($daysAgo);

            return [
                'label' => $day->format('D'),
                'count' => Review::query()->whereDate('created_at', $day)->count(),
            ];
        });

        $recentReviews = Review::query()->with('user')->latest()->limit(5)->get()
            ->map(fn (Review $review): array => [
                'user' => $review->user?->username ?? 'Deleted user',
                'action' => 'reviewed',
                'target' => $review->movie_title,
                'date' => $review->created_at,
                'status' => 'Review',
            ]);
        $recentUsers = User::query()->latest()->limit(5)->get()
            ->map(fn (User $user): array => [
                'user' => $user->username,
                'action' => 'joined MovieREV',
                'target' => 'Community',
                'date' => $user->created_at,
                'status' => 'New member',
            ]);

        $recentActivity = $recentReviews->concat($recentUsers)
            ->sortByDesc(fn (array $activity) => $activity['date']?->timestamp ?? 0)
            ->take(7);

        $topFilms = Review::query()
            ->select('tmdb_id', 'movie_title', DB::raw('count(*) as review_count'))
            ->groupBy('tmdb_id', 'movie_title')
            ->orderByDesc('review_count')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'userCount' => User::query()->count(),
            'filmCount' => Review::query()->distinct('tmdb_id')->count('tmdb_id'),
            'reviewCount' => Review::query()->count(),
            'activeUserCount' => User::query()->where('created_at', '>=', now()->subDays(30))->count(),
            'userGrowth' => $userGrowth,
            'weeklyActivity' => $weeklyActivity,
            'recentActivity' => $recentActivity,
            'topFilms' => $topFilms,
        ]);
    }
}
