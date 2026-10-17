{{--
    The site header, shared by the dashboard and the movie detail page.

    `$search` seeds the search field so a search stays visible on the page it
    returned. Nav targets are `route('home')` + fragment rather than bare
    `#films`, so they work from the movie page too: on the dashboard the
    fragment matches the current document and the browser just scrolls.
--}}
@php($search = $search ?? '')

<header class="sticky top-0 z-50 w-full border-b border-red-500/70 bg-[#0b020d]/95 shadow-[0_12px_32px_-22px_rgba(239,68,68,0.8)] backdrop-blur-md">
    <div class="mx-auto flex min-h-[71px] w-full max-w-none flex-wrap items-center gap-x-4 gap-y-3 px-4 py-3 sm:flex-nowrap sm:gap-x-5 sm:px-6 sm:py-0 lg:gap-x-8 lg:px-8 2xl:px-10">
        <a href="{{ route('home') }}" class="no-underline flex shrink-0 items-center gap-2" aria-label="MovieREV home">
            <img src="{{ asset('img/logo.png') }}" alt="" class="h-12 w-12 object-contain" />
            <span class="font-bebas text-2xl tracking-[0.08em] sm:text-3xl"><span class="text-[#8a4bcd]">MOVIE</span><span class="text-red-500">REV</span></span>
        </a>

        <nav class="order-3 flex w-full items-center justify-center gap-7 font-bebas text-lg tracking-[0.08em] text-stone-300 sm:order-none sm:w-auto sm:shrink-0 sm:justify-start sm:gap-6 sm:text-xl lg:gap-8" aria-label="Primary">
            <a href="{{ route('home') }}" class="no-underline text-white transition-colors hover:text-red-400">Home</a>
            <a href="{{ route('home') }}#films" class="no-underline text-white transition-colors hover:text-red-400">Films</a>
            <a href="{{ route('home') }}#featured" class="no-underline text-white transition-colors hover:text-red-400">Teasers</a>
            <a href="{{ route('home') }}#reviews-week" class="no-underline text-white transition-colors hover:text-red-400">Reviews</a>
        </nav>

        <form method="GET" action="{{ route('home') }}" role="search"
              class="group order-2 ml-auto flex h-9 w-full min-w-[150px] flex-1 items-center gap-2 rounded-full border border-white/10 bg-zinc-900 pl-3.5 pr-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.04)] transition-colors focus-within:border-red-500/70 sm:order-none sm:ml-auto sm:max-w-xs">
            <label for="dashboard-search" class="sr-only">Search movies</label>
            <svg class="h-4 w-4 shrink-0 text-stone-400 transition-colors group-focus-within:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20 20-3.8-3.8M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
            </svg>
            <input id="dashboard-search" name="q" type="search" value="{{ $search }}" placeholder="Search movies" autocomplete="off" class="h-full w-full appearance-none border-0 bg-transparent text-sm text-white outline-none focus:outline-none placeholder:text-stone-400" />
            <button type="submit" class="sr-only">Search</button>
        </form>

        <div class="order-1 ml-auto flex shrink-0 items-center gap-2 sm:order-none sm:ml-0 sm:gap-3">
            @guest
                <a href="{{ route('register') }}" class="no-underline rounded-full border border-white/20 px-5 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:border-red-500/60 hover:bg-white/10 hover:text-red-400 hover:no-underline">Sign Up</a>
                <a href="{{ route('login') }}" class="no-underline rounded-full bg-red-500 px-5 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600 hover:no-underline">Log In</a>
            @else
                @include('partials.account-menu')
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="no-underline rounded-full bg-red-500 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-red-600">Log Out</button>
                </form>
            @endguest
        </div>
    </div>
</header>
