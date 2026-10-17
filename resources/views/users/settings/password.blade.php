@extends('layouts.app')

@section('title', 'Change Password — MovieREV')

@section('content')
    @include('partials.backdrop')

<div class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => ''])

    <main class="mx-auto w-full max-w-2xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
        <a href="{{ route('settings') }}" class="text-xs font-semibold text-stone-400 transition-colors hover:text-white">Back to settings</a>
        <div class="mb-7 mt-5">
            <p class="m-0 text-xs font-bold uppercase tracking-[0.2em] text-red-400">Security</p>
            <h1 class="mt-2 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-4xl">Change Password</h1>
            <p class="mt-2 text-sm text-stone-400">We’ll send a one-time code to your current account email before changing your password.</p>
        </div>

        @if (session('status'))
            <p role="status" class="mb-5 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-200">{{ session('status') }}</p>
        @endif
        @error('email_delivery')<p role="alert" class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ $message }}</p>@enderror

        @if ($passwordCodePending)
            <div class="mb-5 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-300">
                Enter the code sent to <span class="font-semibold text-white">{{ $user->email }}</span> to confirm this change.
            </div>

            <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-5 rounded-xl border border-white/10 bg-[#10020d]/85 p-5 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] sm:p-7">
                @csrf
                @method('PUT')

                <div>
                    <label for="verification_code" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Verification code</label>
                    <input id="verification_code" name="verification_code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm tracking-[0.25em] text-white outline-none transition focus:border-red-500/70">
                    @error('verification_code')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                    @error('password')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                </div>
                <div class="flex justify-end border-t border-white/10 pt-5">
                    <button type="submit" class="rounded-full bg-red-500 px-5 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-red-600">Update Password</button>
                </div>
            </form>

            <form method="POST" action="{{ route('settings.password.code') }}" class="mt-4 space-y-4 rounded-xl border border-white/10 bg-[#10020d]/65 p-5 sm:p-6">
                @csrf
                <p class="m-0 text-sm text-stone-400">Need another code? Confirm your current password to resend it.</p>
                <div>
                    <label for="current_password" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                    @error('current_password')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="text-sm font-semibold text-pink-400 transition-colors hover:text-pink-300">Send another code</button>
            </form>
        @else
            @error('verification_code')<p role="alert" class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ $message }}</p>@enderror
            <form method="POST" action="{{ route('settings.password.code') }}" class="space-y-5 rounded-xl border border-white/10 bg-[#10020d]/85 p-5 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] sm:p-7">
                @csrf

                <div>
                    <label for="current_password" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                    @error('current_password')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end border-t border-white/10 pt-5">
                    <button type="submit" class="rounded-full bg-red-500 px-5 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-red-600">Send Verification Code</button>
                </div>
            </form>
        @endif
    </main>
</div>
@endsection
