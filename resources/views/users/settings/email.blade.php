@extends('layouts.app')

@section('title', 'Change Email — MovieREV')

@section('content')
    @include('partials.backdrop')

<div class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => ''])

    <main class="mx-auto w-full max-w-2xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
        <a href="{{ route('settings') }}" class="text-xs font-semibold text-stone-400 transition-colors hover:text-white">Back to settings</a>
        <div class="mb-7 mt-5">
            <p class="m-0 text-xs font-bold uppercase tracking-[0.2em] text-red-400">Security</p>
            <h1 class="mt-2 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-4xl">Change Email</h1>
            <p class="mt-2 text-sm text-stone-400">Update the email address associated with your account.</p>
        </div>

        <form method="POST" action="{{ route('settings.email.update') }}" class="space-y-5 rounded-xl border border-white/10 bg-[#10020d]/85 p-5 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] sm:p-7">
            @csrf
            @method('PUT')

            <div>
                <label for="email" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">New email address</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                @error('email')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="current_password_email" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Confirm with current password</label>
                <input id="current_password_email" name="current_password_email" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                @error('current_password_email')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end border-t border-white/10 pt-5">
                <button type="submit" class="rounded-full bg-red-500 px-6 py-3 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600">Update email</button>
            </div>
        </form>
    </main>
</div>
@endsection
