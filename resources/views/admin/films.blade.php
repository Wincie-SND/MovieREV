@extends('layouts.app')

@section('title', 'Manage Films | MovieREV')

@section('content')
<div class="min-h-screen bg-[#101010] text-white">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-60 flex-col border-r border-white/10 bg-[#181818] lg:flex">
        <a href="{{ route('home') }}" class="flex h-[69px] items-center gap-3 border-b border-white/10 px-6 no-underline">
            <img src="{{ asset('img/logo.png') }}" alt="" class="h-9 w-9 object-contain">
            <span class="font-bebas text-2xl tracking-wider text-white">MOVIE<span class="text-red-500">REV</span></span>
        </a>
        <nav class="space-y-1 px-3 py-5" aria-label="Admin navigation">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 font-bebas text-lg tracking-wide text-stone-400 transition hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="5" rx="1"/><rect x="13" y="10" width="8" height="11" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.films') }}" class="flex items-center gap-3 rounded-lg bg-stone-800 px-4 py-3 font-bebas text-lg tracking-wide text-white" aria-current="page">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 4v16M17 4v16M3 9h4m-4 6h4m10-6h4m-4 6h4"/></svg>
                Films
            </a>
            <a href="{{ route('admin.dashboard') }}#activity" class="flex items-center gap-3 rounded-lg px-4 py-3 font-bebas text-lg tracking-wide text-stone-400 transition hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M12 3v18m9-9H3"/><circle cx="12" cy="12" r="9"/></svg>
                Reviews
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
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="font-bebas text-xl tracking-wider text-white lg:hidden">MOVIE<span class="text-red-500">REV</span></a>
                <h1 class="font-bebas text-2xl tracking-wide">Films</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-xs text-stone-500 sm:block">{{ now()->format('D, M j, Y') }}</span>
                @include('partials.account-menu')
                <form method="POST" action="{{ route('logout') }}" class="m-0">@csrf<button class="rounded-md border border-white/10 px-3 py-2 text-xs text-stone-300 transition hover:border-red-500/40 hover:text-white" type="submit">Log out</button></form>
            </div>
        </header>

        <div class="mx-auto max-w-[1440px] px-4 py-7 sm:px-8 sm:py-9">
            <div class="mb-7">
                <h2 class="font-bebas text-3xl tracking-wide sm:text-4xl">Manage films</h2>
                <p class="mt-2 text-sm text-stone-500">Browse the TMDB catalog and see how the MovieREV community is reviewing films.</p>
            </div>

            <div class="mb-5 flex gap-7 border-b border-white/10 text-sm font-semibold">
                <a href="{{ route('admin.films') }}" class="border-b-2 border-red-600 px-1 pb-3 text-white">Films</a>
                <span class="cursor-not-allowed px-1 pb-3 text-stone-600" title="Teaser management is not available">Teasers</span>
                <span class="cursor-not-allowed px-1 pb-3 text-stone-600" title="Genre management is provided by TMDB">Genres</span>
                <span class="hidden cursor-not-allowed px-1 pb-3 text-stone-600 sm:inline" title="Official list management is not available">Official Lists</span>
            </div>

            <form method="GET" action="{{ route('admin.films') }}" class="mb-5 grid gap-3 rounded-xl border border-white/5 bg-[#1b1b1b] p-4 sm:grid-cols-[minmax(220px,1fr)_190px_190px_auto] sm:items-center">
                <label class="relative block">
                    <span class="sr-only">Search films by title</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search films by title..." class="h-11 w-full rounded-lg border border-white/5 bg-[#111] pl-10 pr-3 text-sm text-white placeholder:text-stone-600 focus:border-red-500/50 focus:outline-none">
                </label>
                <label>
                    <span class="sr-only">Filter by genre</span>
                    <select name="genre" class="h-11 w-full rounded-lg border border-white/5 bg-[#111] px-3 text-sm text-stone-300 focus:border-red-500/50 focus:outline-none">
                        <option value="">All genres</option>
                        @foreach ($genres as $id => $name)
                            <option value="{{ $id }}" @selected($genreId === (int) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="sr-only">Filter by community review status</span>
                    <select name="status" class="h-11 w-full rounded-lg border border-white/5 bg-[#111] px-3 text-sm text-stone-300 focus:border-red-500/50 focus:outline-none">
                        <option value="all" @selected($reviewStatus === 'all')>All review status</option>
                        <option value="reviewed" @selected($reviewStatus === 'reviewed')>Has reviews</option>
                        <option value="unreviewed" @selected($reviewStatus === 'unreviewed')>No reviews</option>
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="h-11 rounded-lg bg-red-700 px-5 text-sm font-semibold text-white transition hover:bg-red-600">Filter</button>
                    @if ($search !== '' || $genreId || $reviewStatus !== 'all')
                        <a href="{{ route('admin.films') }}" class="flex h-11 items-center rounded-lg border border-white/10 px-4 text-sm text-stone-400 hover:text-white">Clear</a>
                    @endif
                </div>
            </form>

            @if ($catalogError)
                <div class="mb-4 rounded-lg border border-amber-500/20 bg-amber-500/5 px-4 py-3 text-sm text-amber-200">{{ $catalogError }}</div>
            @elseif ($totalResults === 0 && $films->isEmpty())
                <div class="mb-4 rounded-lg border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-stone-400">No catalog results. Add a TMDB API key or access token to load films.</div>
            @endif

            <section class="overflow-hidden rounded-xl border border-white/5 bg-[#1b1b1b]" aria-label="Films">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 px-5 py-4">
                    <div><h3 class="font-bebas text-xl tracking-wide">Film catalog</h3><p class="mt-0.5 text-xs text-stone-600">{{ number_format($totalResults) }} TMDB titles</p></div>
                    <span class="rounded-full border border-white/10 px-3 py-1 text-[11px] text-stone-500">TMDB catalog</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left">
                        <thead><tr class="border-b border-white/10 text-[10px] uppercase tracking-[0.14em] text-stone-600"><th class="px-5 py-4 font-medium">Film</th><th class="px-4 py-4 font-medium">Genre</th><th class="px-4 py-4 font-medium">Year</th><th class="px-4 py-4 font-medium">Rating</th><th class="px-4 py-4 font-medium">Reviews</th><th class="px-4 py-4 font-medium">Status</th><th class="px-5 py-4 text-right font-medium">Action</th></tr></thead>
                        <tbody>
                            @forelse ($films as $row)
                                @php($film = $row['film'])
                                <tr class="border-b border-white/5 transition hover:bg-white/[0.025] last:border-0">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            @if ($film->posterSource())
                                                <img src="{{ $film->posterSource() }}" alt="" class="h-12 w-9 shrink-0 rounded object-cover" loading="lazy">
                                            @else
                                                <span class="flex h-12 w-9 shrink-0 items-center justify-center rounded bg-stone-800 text-[9px] text-stone-600">NO ART</span>
                                            @endif
                                            <div class="min-w-0"><a href="{{ route('movies.show', $film->id) }}" class="line-clamp-2 text-sm font-medium text-stone-200 hover:text-red-300">{{ $film->title }}</a><p class="mt-1 text-[10px] text-stone-600">TMDB #{{ $film->id }}</p></div>
                                        </div>
                                    </td>
                                    <td class="max-w-48 px-4 py-3.5 text-xs leading-5 text-stone-500">{{ $film->genre ?? '—' }}</td>
                                    <td class="px-4 py-3.5 text-sm text-stone-500">{{ $film->release_year ?? '—' }}</td>
                                    <td class="px-4 py-3.5"><span class="inline-flex items-center gap-1 text-sm text-red-400">★ {{ number_format($film->average_rating, 1) }}</span></td>
                                    <td class="px-4 py-3.5 text-sm text-stone-400">{{ number_format($row['community_reviews']) }}</td>
                                    <td class="px-4 py-3.5"><span class="rounded-full px-2.5 py-1 text-[10px] {{ $row['community_reviews'] > 0 ? 'bg-green-500/10 text-green-400' : 'bg-stone-700/40 text-stone-500' }}">{{ $row['community_reviews'] > 0 ? 'Reviewed' : 'No reviews' }}</span></td>
                                    <td class="px-5 py-3.5 text-right"><a href="{{ route('movies.show', $film->id) }}" class="rounded-md border border-white/10 px-3 py-2 text-xs text-stone-300 transition hover:border-red-500/40 hover:text-white">View film</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-stone-600">{{ $catalogError ? 'Catalog results could not be loaded.' : 'No films matched those filters.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 px-5 py-4">
                    @php($rangeStart = $totalResults > 0 ? (($page - 1) * 20) + 1 : 0)
                    @php($rangeEnd = min($page * 20, $totalResults))
                    <p class="text-xs text-stone-600">Showing {{ $rangeStart }}–{{ $rangeEnd }} of {{ number_format($totalResults) }} films</p>
                    <nav class="flex items-center gap-1" aria-label="Film pages">
                        @php($query = request()->except('page'))
                        <a href="{{ $page > 1 ? route('admin.films', $query + ['page' => $page - 1]) : '#' }}" @class(['flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-sm', 'text-stone-300 hover:bg-white/5' => $page > 1, 'pointer-events-none text-stone-700' => $page <= 1]) aria-label="Previous page">‹</a>
                        <span class="flex h-9 min-w-9 items-center justify-center rounded-lg bg-rose-800 px-3 text-sm text-white">{{ $page }}</span>
                        <span class="px-1 text-stone-600">/</span>
                        <span class="px-1 text-xs text-stone-500">{{ max(1, $totalPages) }}</span>
                        <a href="{{ $page < $totalPages ? route('admin.films', $query + ['page' => $page + 1]) : '#' }}" @class(['flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-sm', 'text-stone-300 hover:bg-white/5' => $page < $totalPages, 'pointer-events-none text-stone-700' => $page >= $totalPages]) aria-label="Next page">›</a>
                    </nav>
                </div>
            </section>
            <p class="mt-5 text-center text-[11px] text-stone-700">Movie information is provided by TMDB. Community review counts come from MovieREV.</p>
        </div>
    </main>
</div>
@endsection
