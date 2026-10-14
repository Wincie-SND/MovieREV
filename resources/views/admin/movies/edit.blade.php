@extends('layouts.app')

@section('title', 'Edit Movie — MovieREV')

@section('content')
<div class="min-h-screen bg-[#08040b] px-4 py-10 text-white">
    <div class="mx-auto w-full max-w-2xl rounded-2xl border border-white/10 bg-[#150017] p-6 sm:p-8">
        <p class="font-bebas text-sm tracking-[0.24em] text-red-500">Admin catalog</p>
        <h1 class="font-bebas text-5xl tracking-wide">Edit movie</h1>
        <p class="mt-1 text-sm text-stone-300">Changes are shown immediately on the public dashboard.</p>

        <form method="POST" action="{{ route('admin.movies.update', $movie) }}" class="mt-6">
            @csrf
            @method('PATCH')
            @include('admin.movies._form', ['movie' => $movie])
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-full bg-red-500 px-6 py-2 text-sm font-bold text-white hover:bg-red-600">Save changes</button>
                <a href="{{ route('admin.movies.index') }}" class="rounded-full bg-white/10 px-5 py-2 text-sm font-bold hover:bg-white/20">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
