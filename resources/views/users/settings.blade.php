@extends('layouts.app')

@section('title', 'Settings — MovieREV')

@section('content')
    @include('partials.backdrop')

<div class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => ''])

    <main class="mx-auto w-full max-w-5xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
        <div class="mb-7">
            <p class="m-0 text-xs font-bold uppercase tracking-[0.2em] text-red-400">Account</p>
            <h1 class="mt-2 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-4xl">Settings</h1>
            <p class="mt-2 text-sm text-stone-400">Update the details shown on your MovieREV profile.</p>
        </div>

        @if (session('status'))
            <p role="status" class="mb-5 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-200">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" class="rounded-2xl border border-white/10 bg-[#10020d]/85 p-5 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] sm:p-8">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                    @error('name')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="username" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" autocomplete="username" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                    @error('username')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                    @error('email')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-7 flex flex-col-reverse gap-3 border-t border-white/10 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('users.show', $user->username) }}" class="text-center text-xs font-semibold text-stone-400 transition-colors hover:text-white">View profile</a>
                <button type="submit" class="rounded-full bg-red-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600">Save changes</button>
            </div>
        </form>
    </main>
</div>
@endsection
