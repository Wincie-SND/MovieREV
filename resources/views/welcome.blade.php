@extends('layouts.app')

@section('title', 'Dashboard — MovieREV')

@section('content')
{{--
    Film grain is declared as plain CSS rather than a Tailwind arbitrary value:
    the SVG data URI is full of characters (`%`, `'`, `#`) that Tailwind's
    arbitrary-value parser would mangle.
--}}
<style>
    .dashboard-grain {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='grain'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.82' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23grain)'/%3E%3C/svg%3E");
    }
</style>

<div id="top" class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    {{-- Cinematic page backdrop: the same tilted still as the auth pages, drained
         to a texture and lit by two coloured spotlights, then reined in by a
         vignette and grain. `fixed` + `pointer-events-none` keeps it off the
         scroll and out of the way of clicks; it is first in tree order, so every
         positioned sibling below (`header`, `main`) paints on top of it. --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <img src="{{ asset('img/forms_background.jpg') }}" alt=""
             class="absolute left-1/2 top-1/2 h-[150%] w-[150%] max-w-none -translate-x-1/2 -translate-y-1/2 -rotate-3 object-cover opacity-[0.12]" />
        <div class="absolute inset-0 [background:radial-gradient(58%_42%_at_15%_0%,rgba(239,68,68,0.22),transparent_62%),radial-gradient(52%_46%_at_100%_6%,rgba(168,85,247,0.18),transparent_64%)]"></div>
        <div class="absolute inset-0 [background:radial-gradient(ellipse_at_center,rgba(0,0,0,0)_36%,rgba(0,0,0,0.55)_76%,rgba(0,0,0,0.92)_100%)]"></div>
        <div class="dashboard-grain absolute inset-0 opacity-[0.05] mix-blend-overlay"></div>
    </div>

    <header class="sticky top-0 z-40 border-b border-red-600/50 bg-gradient-to-b from-black/95 via-black/80 to-black/55 shadow-[0_18px_40px_-26px_rgba(239,68,68,0.6)] backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center gap-x-5 gap-y-3 px-4 py-3">
            <a href="{{ route('home') }}" class="no-underline flex items-center gap-2" aria-label="MovieREV home">
                <img src="{{ asset('img/logo.png') }}" alt="" class="h-8 w-8 object-contain" />
                <span class="font-bebas text-2xl tracking-[0.16em]">MOVIE<span class="text-red-500">REV</span></span>
            </a>

            <nav class="hidden items-center gap-5 text-xs font-bold tracking-[0.18em] text-stone-300 md:flex" aria-label="Primary">
                <a href="#top" class="no-underline text-white hover:text-red-400">Home</a>
                <a href="#films" class="no-underline hover:text-red-400">Films</a>
                <a href="#featured" class="no-underline hover:text-red-400">Teasers</a>
            </nav>

            <form method="GET" action="{{ route('home') }}" role="search"
                  class="group ml-auto flex h-9 min-w-[180px] flex-1 items-center gap-2 rounded-full border border-white/10 bg-[#2A0A2E]/90 pl-3.5 pr-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04),0_10px_30px_-20px_rgba(0,0,0,0.9)] backdrop-blur transition-colors focus-within:border-red-500/60 focus-within:ring-1 focus-within:ring-red-500/30 sm:max-w-xs">
                <label for="dashboard-search" class="sr-only">Search movies</label>
                <svg class="h-4 w-4 shrink-0 text-stone-400 transition-colors group-focus-within:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20 20-3.8-3.8M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
                </svg>
                <input id="dashboard-search" name="q" type="search" value="{{ $search }}" placeholder="Search movies" autocomplete="off" class="h-full w-full bg-transparent text-sm text-white outline-none placeholder:text-stone-400 [&::-webkit-search-cancel-button]:hidden" />
                <button type="submit" class="sr-only">Search</button>
            </form>

            <div class="flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="no-underline rounded-full bg-red-500 px-5 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600 hover:no-underline">Log In</a>
                @else
                    @if (auth()->user()?->usertype === 'admin')
                        <a href="{{ route('admin.movies.index') }}" class="no-underline rounded-full bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-white/20 hover:no-underline">Manage Movies</a>
                    @endif
                    <span class="hidden items-center gap-2 text-xs font-bold uppercase tracking-[0.12em] text-stone-200 sm:flex">
                        <span aria-hidden="true" class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/10 font-bebas text-sm">{{ mb_strtoupper(mb_substr(Auth::user()->username, 0, 1)) }}</span>
                        <span class="max-w-[120px] truncate">{{ Auth::user()->username }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="no-underline rounded-full border border-white/15 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-white/10">Log Out</button>
                    </form>
                @endguest
            </div>
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-6xl px-4 pb-16">
        @if ($search !== '')
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-200 backdrop-blur-sm">
                Showing catalog matches for <strong class="text-white">“{{ $search }}”</strong>.
                @if (! $featuredMovie && $popularMovies->isEmpty() && $recentReviews->isEmpty())
                    No matches were found, so placeholder slots are shown below.
                @endif
            </p>
        @endif

        <section id="featured" aria-label="Featured movie" class="mt-6 overflow-hidden rounded-2xl border border-white/10 bg-[#10020d]/85 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] backdrop-blur-sm">
            @if ($featuredMovie)
                @php($featuredPoster = $featuredMovie->posterSource())
                <div class="grid md:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
                    <div class="relative min-h-[280px] overflow-hidden md:min-h-[360px]">
                        @if ($featuredPoster)
                            <img src="{{ str_starts_with($featuredPoster, 'http') ? $featuredPoster : asset(ltrim($featuredPoster, '/')) }}" alt="{{ $featuredMovie->title }} featured art" class="absolute inset-0 h-full w-full object-cover" />
                        @else
                            <div class="absolute inset-0 [background:radial-gradient(circle_at_30%_20%,rgba(239,68,68,0.28),transparent_45%),linear-gradient(135deg,#2a0a2e,#0b020d_70%)]" aria-hidden="true"></div>
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 p-6 text-center">
                                <p class="font-bebas text-5xl tracking-[0.12em] text-white/25">{{ \Illuminate\Support\Str::limit(mb_strtoupper($featuredMovie->title), 22) }}</p>
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/45">Featured art placeholder</p>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#10020d] via-transparent to-transparent md:bg-gradient-to-r" aria-hidden="true"></div>
                    </div>
                    <div class="flex flex-col justify-center p-6 sm:p-10">
                        <p class="font-bebas text-sm tracking-[0.28em] text-red-500">Featured</p>
                        <h1 class="mt-1 font-bebas text-6xl leading-[0.95] tracking-wide sm:text-7xl">{{ $featuredMovie->title }}</h1>
                        <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-300">
                            {{ $featuredMovie->release_year ?? 'Year TBD' }}
                            @if ($featuredMovie->genre)
                                • {{ $featuredMovie->genre }}
                            @endif
                            • ★ {{ number_format((float) ($featuredMovie->average_rating ?? 0), 1) }}
                        </p>
                        <p class="mt-4 max-w-xl text-sm leading-relaxed text-stone-200">
                            {{ $featuredMovie->description ? \Illuminate\Support\Str::limit($featuredMovie->description, 240) : 'A full description has not been entered yet. This slot keeps the hero layout intact until an administrator adds one.' }}
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="#movie-{{ $featuredMovie->id }}" class="no-underline rounded-full bg-red-500 px-6 py-2 text-sm font-bold text-white hover:bg-red-600 hover:no-underline">View Film</a>
                            <a href="#films" class="no-underline rounded-full border border-white/20 px-6 py-2 text-sm font-bold text-white hover:bg-white/10 hover:no-underline">Browse Films</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="relative overflow-hidden p-8 sm:p-12">
                    <div class="absolute inset-0 [background:radial-gradient(circle_at_30%_20%,rgba(239,68,68,0.22),transparent_45%),linear-gradient(135deg,#2a0a2e,#0b020d_70%)]" aria-hidden="true"></div>
                    <div class="relative">
                        <p class="font-bebas text-sm tracking-[0.28em] text-red-500">Featured</p>
                        <h1 class="mt-1 font-bebas text-6xl leading-[0.95] sm:text-7xl">Featured title placeholder</h1>
                        <p class="mt-3 max-w-xl text-sm leading-relaxed text-stone-200">No catalog entries are available yet. An administrator can add the first movie, and this hero will use its title, metadata, and poster or placeholder.</p>
                        @if (auth()->user()?->usertype === 'admin')
                            <a href="{{ route('admin.movies.create') }}" class="no-underline mt-6 inline-block rounded-full bg-red-500 px-6 py-2 text-sm font-bold text-white hover:bg-red-600 hover:no-underline">Add the first movie</a>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <section id="recently-reviewed" aria-labelledby="recently-reviewed-heading" class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 id="recently-reviewed-heading" class="font-bebas text-4xl tracking-wide">Recently Reviewed</h2>
                <p class="hidden text-xs uppercase tracking-[0.18em] text-stone-400 sm:block">{{ $recentlyReviewedMovies->count() }} titles</p>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @forelse ($recentlyReviewedMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @empty
                    @for ($placeholder = 0; $placeholder < 5; $placeholder++)
                        <div>
                            <x-poster-art title="Placeholder title" subtitle="Awaiting review" />
                            <p class="mt-2 font-bebas text-lg leading-none tracking-wide text-stone-300">Title placeholder</p>
                            <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-stone-500">Year placeholder</p>
                            <div class="mt-1"><x-rating-stars :value="0" /></div>
                        </div>
                    @endfor
                @endforelse
            </div>
            @if ($recentlyReviewedMovies->isEmpty())
                <p class="mt-3 text-sm text-stone-400">Placeholder slots are shown until reviews are entered. No review content has been invented.</p>
            @endif
        </section>

        <section id="films" aria-labelledby="popular-films-heading" class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 id="popular-films-heading" class="font-bebas text-4xl tracking-wide">Popular Films</h2>
                <p class="hidden text-xs uppercase tracking-[0.18em] text-stone-400 sm:block">{{ $movieCount }} catalogued</p>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
                @forelse ($popularMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @empty
                    @for ($placeholder = 0; $placeholder < 4; $placeholder++)
                        <div>
                            <x-poster-art title="Placeholder title" subtitle="Awaiting entry" />
                            <p class="mt-2 font-bebas text-lg leading-none tracking-wide text-stone-300">Title placeholder</p>
                            <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-stone-500">Year placeholder</p>
                            <div class="mt-1"><x-rating-stars :value="0" /></div>
                        </div>
                    @endfor
                @endforelse
            </div>
            @if ($popularMovies->isEmpty())
                <p class="mt-3 text-sm text-stone-400">Placeholder slots are shown until an administrator adds movies.</p>
            @endif
        </section>

        <section id="reviews-week" aria-labelledby="popular-reviews-heading" class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 id="popular-reviews-heading" class="font-bebas text-4xl tracking-wide">Popular Reviews This Week</h2>
                <a href="#reviews-week" class="no-underline text-xs font-bold uppercase tracking-[0.18em] text-red-400 hover:text-red-300 hover:underline">See More &gt;</a>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @forelse ($recentReviews as $review)
                    <x-review-card :review="$review" />
                @empty
                    @for ($placeholder = 0; $placeholder < 2; $placeholder++)
                        <div class="rounded-xl border border-white/10 bg-[#2A0A2E]/60 p-4 backdrop-blur-sm">
                            <div class="flex items-start gap-3">
                                <div class="w-16 shrink-0"><x-poster-art title="Review placeholder" subtitle="Placeholder" /></div>
                                <div>
                                    <p class="font-bebas text-2xl leading-none tracking-wide text-stone-300">Review placeholder</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-500">Awaiting member review</p>
                                    <div class="mt-2"><x-rating-stars :value="0" /></div>
                                </div>
                            </div>
                            <p class="mt-3 text-xs leading-relaxed text-stone-400">Review cards will appear here after members submit reviews for catalogued movies.</p>
                        </div>
                    @endfor
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection
