{{--
    The site header, shared by the dashboard and the movie detail page.

    `$search` seeds the search field so a search stays visible on the page it
    returned. Nav targets are `route('home')` + fragment rather than bare
    `#films`, so they work from the movie page too: on the dashboard the
    fragment matches the current document and the browser just scrolls.
--}}
@php($search = $search ?? '')

<header class="sticky top-0 z-40 border-b border-red-600/50 bg-gradient-to-b from-black/95 via-black/80 to-black/55 shadow-[0_18px_40px_-26px_rgba(239,68,68,0.6)] backdrop-blur">
    <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center gap-x-5 gap-y-3 px-4 py-3">
        <a href="{{ route('home') }}" class="no-underline flex items-center gap-2" aria-label="MovieREV home">
            <img src="{{ asset('img/logo.png') }}" alt="" class="h-8 w-8 object-contain" />
            <span class="font-bebas text-2xl tracking-[0.16em]">MOVIE<span class="text-red-500">REV</span></span>
        </a>

        <nav class="hidden items-center gap-5 text-xs font-bold tracking-[0.18em] text-stone-300 md:flex" aria-label="Primary">
            <a href="{{ route('home') }}" class="no-underline text-white hover:text-red-400">Home</a>
            <a href="{{ route('home') }}#films" class="no-underline hover:text-red-400">Films</a>
            <a href="{{ route('home') }}#featured" class="no-underline hover:text-red-400">Teasers</a>
        </nav>

        <form method="GET" action="{{ route('home') }}" role="search"
              class="group ml-auto flex h-9 min-w-[180px] flex-1 items-center gap-2 rounded-full border border-white/10 bg-[#2A0A2E]/90 pl-3.5 pr-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04),0_10px_30px_-20px_rgba(0,0,0,0.9)] backdrop-blur transition-colors focus-within:border-red-500/50 sm:max-w-xs">
            <label for="dashboard-search" class="sr-only">Search movies</label>
            <svg class="h-4 w-4 shrink-0 text-stone-400 transition-colors group-focus-within:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20 20-3.8-3.8M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
            </svg>
            <input id="dashboard-search" name="q" type="search" value="{{ $search }}" placeholder="Search movies" autocomplete="off" class="h-full w-full appearance-none border-0 bg-transparent text-sm text-white outline-none focus:outline-none placeholder:text-stone-400" />
            <button type="submit" class="sr-only">Search</button>
        </form>

        <div class="flex items-center gap-3">
            @guest
                <a href="{{ route('register') }}" class="no-underline rounded-full border border-white/20 px-5 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:border-red-500/60 hover:bg-white/10 hover:text-red-400 hover:no-underline">Sign Up</a>
                <a href="{{ route('login') }}" class="no-underline rounded-full bg-red-500 px-5 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600 hover:no-underline">Log In</a>
            @else
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
