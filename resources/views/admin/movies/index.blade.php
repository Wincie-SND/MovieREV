@extends('layouts.app')

@section('title', 'Manage Movies — MovieREV')

@section('content')
<div class="min-h-screen bg-[#08040b] px-4 py-10 text-white">
    <div class="mx-auto w-full max-w-5xl">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="font-bebas text-sm tracking-[0.24em] text-red-500">Admin catalog</p>
                <h1 class="font-bebas text-5xl tracking-wide">Manage movies</h1>
                <p class="mt-1 max-w-xl text-sm text-stone-300">Movies added here appear on the public dashboard. Leave the poster field blank to use an automatic placeholder.</p>
            </div>
            <a href="{{ route('admin.movies.create') }}" class="rounded-full bg-red-500 px-5 py-2 text-sm font-bold text-white hover:bg-red-600">Add movie</a>
        </div>

        <form method="GET" action="{{ route('admin.movies.index') }}" class="mt-6 flex max-w-md items-center gap-2" role="search">
            <label class="sr-only" for="admin-movie-search">Search movies</label>
            <input id="admin-movie-search" type="search" name="q" value="{{ $search }}" placeholder="Search title, genre, or description" class="h-10 w-full rounded-full border border-white/10 bg-[#2A0A2E] px-4 text-sm text-white outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500" />
            <button type="submit" class="h-10 shrink-0 rounded-full bg-white/10 px-4 text-sm font-bold hover:bg-white/20">Search</button>
        </form>

        @if ($movies->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-white/15 bg-white/5 p-8 text-center">
                <h2 class="font-bebas text-3xl tracking-wide">No movies yet</h2>
                <p class="mt-1 text-sm text-stone-300">Add the first catalog entry. Placeholders will be shown until poster art is supplied.</p>
                <a href="{{ route('admin.movies.create') }}" class="mt-4 inline-block rounded-full bg-red-500 px-5 py-2 text-sm font-bold text-white hover:bg-red-600">Add the first movie</a>
            </div>
        @else
            <div class="mt-6 overflow-hidden rounded-2xl border border-white/10 bg-[#150017]">
                @foreach ($movies as $movie)
                    <article class="flex flex-col gap-4 border-b border-white/10 p-4 last:border-b-0 sm:flex-row sm:items-center">
                        <div class="w-24 shrink-0">
                            @php($poster = $movie->posterSource())
                            @if ($poster)
                                <img src="{{ str_starts_with($poster, 'http') ? $poster : asset(ltrim($poster, '/')) }}" alt="{{ $movie->title }} poster" class="aspect-[2/3] w-full rounded-lg border border-white/10 object-cover" loading="lazy" />
                            @else
                                <x-poster-art :title="$movie->title" subtitle="Placeholder" />
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-bebas text-3xl leading-none tracking-wide">{{ $movie->title }}</h2>
                            <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-400">
                                {{ $movie->release_year ?? 'Year to be announced' }}
                                @if ($movie->genre)
                                    • {{ $movie->genre }}
                                @endif
                            </p>
                            <p class="mt-2 line-clamp-2 text-sm text-stone-300">{{ $movie->description ?: 'No description entered yet.' }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('admin.movies.edit', $movie) }}" class="rounded-full bg-white/10 px-4 py-2 text-xs font-bold hover:bg-white/20">Edit</a>
                            <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}" onsubmit="return confirm('Remove {{ addslashes($movie->title) }} from the catalog?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-full bg-red-500/15 px-4 py-2 text-xs font-bold text-red-300 hover:bg-red-500/30">Delete</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
