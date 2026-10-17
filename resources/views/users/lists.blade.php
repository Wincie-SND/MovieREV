@extends('layouts.app')

@section('title', 'My Movie Lists | MovieREV')

@section('content')
    @include('partials.backdrop')

<div id="top" class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => ''])

    <main class="mx-auto w-full max-w-[1800px] px-4 pb-16 pt-8 sm:px-6 lg:px-8 2xl:px-10">
        @if (session('status'))
            <p role="status" class="mb-6 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-200">{{ session('status') }}</p>
        @endif

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="m-0 text-xs font-bold uppercase tracking-[0.2em] text-red-400">Your collection</p>
                <h1 class="mt-2 text-4xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">My Movie Lists</h1>
                <p class="mt-2 max-w-2xl text-sm text-stone-400">Keep the movies you love close and save new discoveries to rate later.</p>
            </div>
            <a href="{{ route('home') }}#films" class="no-underline rounded-full bg-red-500 px-5 py-3 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600">Browse films</a>
        </div>

        @foreach ($lists as $list)
            <section class="mt-10" aria-labelledby="list-{{ $list['slug'] }}-heading">
                <div class="flex items-baseline justify-between gap-4 border-b border-white/10 pb-3">
                    <div>
                        <h2 id="list-{{ $list['slug'] }}-heading" class="m-0 text-2xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">{{ $list['name'] }}</h2>
                        <p class="mt-1 text-xs text-stone-500">
                            @if ($list['slug'] === 'favorites')
                                Your favorite movies.
                            @else
                                Movies you want to rate when you’re ready.
                            @endif
                        </p>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-[0.14em] text-stone-500">{{ $list['movies']->count() }} {{ \Illuminate\Support\Str::plural('movie', $list['movies']->count()) }}</span>
                </div>

                @if ($list['movies']->isNotEmpty())
                    <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                        @foreach ($list['movies'] as $item)
                            @php($title = $item['movie']?->title ?? ('Saved film #'.$item['id']))
                            <article class="group overflow-hidden rounded-xl border border-white/10 bg-[#150017]/85 transition-colors hover:border-red-500/50">
                                <a href="{{ route('movies.show', $item['id']) }}" class="no-underline block hover:no-underline">
                                    @if ($item['movie']?->posterSource())
                                        <img src="{{ $item['movie']->posterSource() }}" alt="{{ $title }} poster" class="aspect-[2/3] w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]" loading="lazy">
                                    @else
                                        <div class="flex aspect-[2/3] items-center justify-center bg-gradient-to-br from-fuchsia-950 to-zinc-950 p-4 text-center font-bebas text-xl text-white/80">{{ $title }}</div>
                                    @endif
                                    <div class="p-3">
                                        <h3 class="m-0 truncate font-bebas text-xl tracking-wide text-white group-hover:text-red-300">{{ $title }}</h3>
                                        <p class="mt-1 text-[10px] uppercase tracking-[0.12em] text-stone-500">Added {{ \Illuminate\Support\Carbon::parse($item['added_at'])->format('M j, Y') }}</p>
                                    </div>
                                </a>
                                <form method="POST" action="{{ route('movie-lists.destroy', ['movie' => $item['id'], 'list' => $list['slug']]) }}" class="border-t border-white/10 p-2">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_to" value="lists">
                                    <button type="submit" class="w-full rounded-md px-3 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-stone-400 transition-colors hover:bg-red-500/10 hover:text-red-300">Remove from {{ $list['name'] }}</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="mt-5 rounded-xl border border-dashed border-white/15 bg-white/[0.03] px-5 py-9 text-center">
                        <p class="m-0 font-bebas text-xl tracking-wide text-stone-300">This list is empty</p>
                        <p class="mt-1 text-sm text-stone-500">Open a movie and add it to {{ $list['name'] }}.</p>
                    </div>
                @endif
            </section>
        @endforeach
    </main>
</div>
@endsection
