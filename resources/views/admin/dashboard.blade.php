@extends('layouts.app')

@section('title', 'Admin Dashboard | MovieREV')

@section('content')
<div class="min-h-screen bg-[#101010] text-white">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-60 flex-col border-r border-white/10 bg-[#181818] lg:flex">
        <a href="{{ route('home') }}" class="flex h-[69px] items-center gap-3 border-b border-white/10 px-6 no-underline">
            <img src="{{ asset('img/logo.png') }}" alt="" class="h-9 w-9 object-contain">
            <span class="font-bebas text-2xl tracking-wider text-white">MOVIE<span class="text-red-500">REV</span></span>
        </a>
        <nav class="space-y-1 px-3 py-5" aria-label="Admin navigation">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg bg-stone-800 px-4 py-3 font-bebas text-lg tracking-wide text-white" aria-current="page">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="10" width="8" height="11" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.films') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 font-bebas text-lg tracking-wide text-stone-400 transition hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 4v16M17 4v16M3 9h4m-4 6h4m10-6h4m-4 6h4"/></svg>
                Films
            </a>
            <a href="#activity" class="flex items-center gap-3 rounded-lg px-4 py-3 font-bebas text-lg tracking-wide text-stone-400 transition hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 3v18m9-9H3"/><circle cx="12" cy="12" r="9"/></svg>
                Reviews
            </a>
            <a href="#members" class="flex items-center gap-3 rounded-lg px-4 py-3 font-bebas text-lg tracking-wide text-stone-400 transition hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75"/><circle cx="10" cy="7" r="4"/></svg>
                Members
            </a>
        </nav>
        <div class="mt-auto border-t border-white/10 p-4">
            <a href="{{ route('home') }}" class="mb-4 flex items-center gap-3 px-3 py-2 text-sm text-stone-400 transition hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6M9 12h12"/></svg>
                Back to site
            </a>
            <div class="flex items-center gap-3 border-t border-white/10 px-2 pt-4">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-950 font-bebas text-lg text-red-300">{{ mb_strtoupper(mb_substr(Auth::user()->username, 0, 1)) }}</span>
                <div class="min-w-0"><p class="truncate text-sm font-medium">{{ Auth::user()->name }}</p><p class="text-xs text-stone-500">Administrator</p></div>
            </div>
        </div>
    </aside>

    <main class="lg:pl-60">
        <header class="sticky top-0 z-20 flex h-[69px] items-center justify-between border-b border-white/10 bg-[#101010]/95 px-5 backdrop-blur sm:px-8">
            <div class="flex items-center gap-3 lg:gap-0">
                <a href="{{ route('home') }}" class="font-bebas text-xl tracking-wider text-white lg:hidden">MOVIE<span class="text-red-500">REV</span></a>
                <h1 class="hidden font-bebas text-2xl tracking-wide sm:block">Admin Dashboard</h1>
                <h1 class="font-bebas text-xl tracking-wide sm:hidden">Dashboard</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-xs text-stone-500 sm:block">{{ now()->format('D, M j, Y') }}</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-stone-900 font-bebas text-lg text-red-300" title="{{ Auth::user()->name }}">{{ mb_strtoupper(mb_substr(Auth::user()->username, 0, 1)) }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf<button class="rounded-md border border-white/10 px-3 py-2 text-xs text-stone-300 transition hover:border-red-500/40 hover:text-white" type="submit">Log out</button></form>
            </div>
        </header>

        <div class="mx-auto max-w-[1440px] space-y-6 px-4 py-6 sm:px-8 sm:py-8">
            <section class="grid grid-cols-2 gap-3 xl:grid-cols-4 xl:gap-5" aria-label="Community overview">
                @foreach ([['Total users', $userCount, 'people joined'], ['Reviewed films', $filmCount, 'unique TMDB titles'], ['Total reviews', $reviewCount, 'community reviews'], ['New members', $activeUserCount, 'joined in the last 30 days']] as [$label, $value, $note])
                    <article class="rounded-xl border border-white/5 bg-[#1b1b1b] p-4 sm:p-5">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xs font-medium text-stone-500 sm:text-sm">{{ $label }}</p>
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-950/60 text-red-400">
                                @if ($label === 'Total users' || $label === 'New members')
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M14 3.13a4 4 0 0 1 0 7.75"/><circle cx="10" cy="7" r="4"/></svg>
                                @elseif ($label === 'Reviewed films')
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 4v16M17 4v16"/></svg>
                                @else
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>
                                @endif
                            </span>
                        </div>
                        <p class="mt-5 font-bebas text-3xl sm:text-4xl">{{ number_format($value) }}</p>
                        <p class="mt-1 text-[11px] text-stone-600">{{ $note }}</p>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-5 xl:grid-cols-[1.7fr_1fr]">
                <article class="rounded-xl border border-white/5 bg-[#1b1b1b] p-5 sm:p-6">
                    <div class="flex items-start justify-between"><div><h2 class="font-bebas text-2xl tracking-wide">User growth</h2><p class="mt-1 text-xs text-stone-500">Cumulative registered members by month</p></div><span class="rounded-md border border-white/10 px-2.5 py-1 text-[10px] text-stone-500">6 months</span></div>
                    @php
                        $growthMax = max(1, (int) $userGrowth->max('count'));
                        $growthPoints = $userGrowth->values()->map(fn ($point, $index) => (24 + $index * 88).','. (190 - (int) round($point['count'] / $growthMax * 150)))->implode(' ');
                    @endphp
                    <div class="mt-5 overflow-hidden">
                        <svg viewBox="0 0 480 220" class="h-52 w-full" role="img" aria-label="User growth chart">
                            @foreach ([35, 85, 135, 185] as $y)<line x1="20" y1="{{ $y }}" x2="470" y2="{{ $y }}" stroke="#333" stroke-dasharray="3 5"/>@endforeach
                            <polyline points="{{ $growthPoints }}" fill="none" stroke="#ef4444" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            @foreach ($userGrowth->values() as $index => $point)
                                @php($cx = 24 + $index * 88)
                                @php($cy = 190 - (int) round($point['count'] / $growthMax * 150))
                                <circle cx="{{ $cx }}" cy="{{ $cy }}" r="4" fill="#ef4444" stroke="#1b1b1b" stroke-width="2"/>
                                <text x="{{ $cx }}" y="215" text-anchor="middle" fill="#78716c" font-size="11">{{ $point['label'] }}</text>
                            @endforeach
                        </svg>
                    </div>
                </article>
                <article class="rounded-xl border border-white/5 bg-[#1b1b1b] p-5 sm:p-6">
                    <div><h2 class="font-bebas text-2xl tracking-wide">Most reviewed films</h2><p class="mt-1 text-xs text-stone-500">From community reviews</p></div>
                    <div class="mt-5 space-y-4">
                        @forelse ($topFilms as $film)
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-950/60 font-bebas text-lg text-red-300">{{ $loop->iteration }}</span>
                                <div class="min-w-0 flex-1"><p class="truncate text-sm text-stone-200">{{ $film->movie_title }}</p><p class="text-[11px] text-stone-600">{{ number_format($film->review_count) }} {{ \Illuminate\Support\Str::plural('review', $film->review_count) }}</p></div>
                                <a href="{{ route('movies.show', $film->tmdb_id) }}" class="text-xs text-red-400 hover:text-red-300">View</a>
                            </div>
                        @empty
                            <div class="flex min-h-40 items-center justify-center rounded-lg border border-dashed border-white/10 px-5 text-center text-sm text-stone-600">Film rankings will appear when members post reviews.</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="grid gap-5 xl:grid-cols-[1fr_1.45fr]">
                <article class="rounded-xl border border-white/5 bg-[#1b1b1b] p-5 sm:p-6">
                    <div><h2 class="font-bebas text-2xl tracking-wide">Weekly activity</h2><p class="mt-1 text-xs text-stone-500">Reviews posted over the last seven days</p></div>
                    @php($activityMax = max(1, (int) $weeklyActivity->max('count')))
                    <div class="mt-7 flex h-44 items-end justify-between gap-2">
                        @foreach ($weeklyActivity as $day)
                            <div class="flex h-full flex-1 flex-col items-center justify-end gap-2">
                                <span class="text-[10px] text-stone-500">{{ $day['count'] ?: '' }}</span>
                                <div class="w-full max-w-8 rounded-t bg-red-500/80" style="height: {{ max(5, (int) round($day['count'] / $activityMax * 110)) }}px"></div>
                                <span class="text-[10px] text-stone-600">{{ $day['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article id="activity" class="scroll-mt-24 rounded-xl border border-white/5 bg-[#1b1b1b] p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3"><div><h2 class="font-bebas text-2xl tracking-wide">Recent activity</h2><p class="mt-1 text-xs text-stone-500">Latest community updates</p></div><a href="#activity" class="text-xs font-medium text-red-400 hover:text-red-300">Latest</a></div>
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[510px] text-left text-xs">
                            <thead><tr class="border-y border-white/10 text-[10px] uppercase tracking-wider text-stone-600"><th class="py-3 pr-3 font-medium">Member</th><th class="py-3 pr-3 font-medium">Activity</th><th class="py-3 pr-3 font-medium">Date</th><th class="py-3 font-medium">Type</th></tr></thead>
                            <tbody>
                                @forelse ($recentActivity as $activity)
                                    <tr class="border-b border-white/5 last:border-0">
                                        <td class="py-3 pr-3 font-medium text-stone-300">{{ $activity['user'] }}</td>
                                        <td class="py-3 pr-3 text-stone-500">{{ $activity['action'] }} <span class="text-stone-300">{{ $activity['target'] }}</span></td>
                                        <td class="whitespace-nowrap py-3 pr-3 text-stone-600">{{ $activity['date']?->format('M j') ?? '—' }}</td>
                                        <td class="py-3"><span class="rounded-full bg-white/5 px-2 py-1 text-[10px] text-stone-400">{{ $activity['status'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="py-8 text-center text-stone-600">No activity to show yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
            <p id="members" class="pb-3 text-center text-[11px] text-stone-700">MovieREV administration · {{ Auth::user()->username }}</p>
        </div>
    </main>
</div>
@endsection
