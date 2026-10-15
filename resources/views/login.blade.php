@extends('layouts.app')

@section('title', 'Sign In — MovieREV')

@section('content')
<div class="min-h-screen w-full relative flex [background:linear-gradient(270deg,#000000_10%,#4a044e_41%,#000000_85%)]">

    {{-- Page backdrop. The still is tilted and oversized so the rotation never exposes
         a corner; the wrapper's `overflow-hidden` clips that overscan. `fixed` (not
         `absolute`) keeps it covering the viewport even when the layout's 100vh vs
         scrollbar mismatch allows a few px of scroll. Vignette pulls the edges down.
         The frame below is positioned and later in tree order, so it paints on top
         without needing a z-index. --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <img src="{{ asset('img/forms_background.jpg') }}" alt=""
             class="absolute left-1/2 top-1/2 h-[150%] w-[150%] max-w-none -translate-x-1/2 -translate-y-1/2 -rotate-3 object-cover opacity-[0.28]"/>
        <div class="absolute inset-0 [background:radial-gradient(ellipse_at_center,rgba(0,0,0,0)_35%,rgba(0,0,0,0.5)_75%,rgba(0,0,0,0.88)_100%)]"></div>
    </div>

    {{-- `m-auto` centres the fixed 1201x670 frame when there is room, and lets the
         page scroll to it when the viewport is smaller (flex `justify-center`
         would make the overflowing edge unreachable). --}}
    <div class="m-auto relative w-[1201px] h-[670px] shrink-0">

        {{-- Ambient still, bled in at 10% opacity and clipped to the frame --}}
        <div class="absolute inset-0 overflow-hidden">
            <img class="absolute left-0 top-[-401px] w-[1267px] h-[1472px] opacity-10 object-cover pointer-events-none select-none" src="{{ asset('img/image-4.png') }}" alt=""/>
        </div>

        {{-- Hero copy --}}
        <h1 class="absolute left-[14px] top-[283px] m-0 text-white text-4xl font-bold leading-[1.15] [font-family:'Playfair_Display',Georgia,serif]">
            Your film life,<br/>
             all in <span class="text-red-500">one place.</span>
        </h1>

        {{-- Sign-in card --}}
        <form method="POST" action="{{ route('login.attempt') }}"
              class="absolute left-[659px] top-[70px] w-[455px] min-h-[575px] bg-[#150017] rounded-xl flex flex-col">
            @csrf

            {{-- Brand mark. The PNG is a 500x500 canvas with ~12% transparent padding
                 on every side, so a 72px box renders the mark itself at ~56px. --}}
            <img src="{{ asset('img/logo.png') }}" alt="MovieREV"
                 class="mx-auto mt-[22px] block w-[72px] h-[72px] shrink-0 object-contain"/>

            <h2 class="m-0 pt-[10px] text-center text-white text-4xl font-bold leading-[1.15] [font-family:'Playfair_Display',Georgia,serif]">Sign In</h2>

            {{-- `my-auto` absorbs the height difference between this shorter form
                 and the 5-field sign-up form, so both cards render identically. --}}
            <div class="my-auto flex flex-col items-center gap-[9px] pt-[24px]">
                {{-- Email or username --}}
                <div>
                    <label for="identifier" class="block mb-[1px] text-stone-300 text-[8px] font-bold uppercase leading-[14px] tracking-[1px] [font-family:'Inter',Helvetica,sans-serif]">Email or Username</label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        value="{{ old('identifier') }}"
                        placeholder="you@example.com"
                        autocomplete="username"
                        autofocus
                        class="w-80 h-8 bg-[#2A0A2E] border-0 px-[23px] text-white text-xs [font-family:'Inter',Helvetica,sans-serif] outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500"
                    />
                    @error('identifier')
                        <p class="m-0 mt-[3px] text-red-400 text-[10px] [font-family:'Inter',Helvetica,sans-serif]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block mb-[1px] text-stone-300 text-[8px] font-bold uppercase leading-[14px] tracking-[1px] [font-family:'Inter',Helvetica,sans-serif]">Password</label>
                    <div class="relative w-80">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-80 h-8 bg-[#2A0A2E] border-0 pl-[23px] pr-[30px] text-white text-xs [font-family:'Inter',Helvetica,sans-serif] outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500"
                        />
                        <button
                            type="button"
                            data-password-toggle
                            aria-label="Show password"
                            aria-pressed="false"
                            class="absolute right-[4px] top-[4px] w-6 h-6 flex items-center justify-center bg-transparent border-0 p-0 cursor-pointer text-stone-400 hover:text-white"
                        >
                            <svg class="eye-open w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg class="eye-off hidden w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="m-0 mt-[3px] text-red-400 text-[10px] [font-family:'Inter',Helvetica,sans-serif]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me: ticked issues a persistent login cookie, unticked is session-only --}}
                <label class="flex items-center gap-[7px] cursor-pointer text-stone-300 text-[8px] font-bold leading-[14px] tracking-[1px] [font-family:'Inter',Helvetica,sans-serif]">
                    <input type="checkbox" name="remember" value="1" class="m-0 w-3 h-3 accent-red-500"/>
                    Remember me
                </label>

                <button type="submit" class="mt-[5px] w-80 h-14 bg-red-500 hover:bg-red-600 border-0 cursor-pointer flex items-center justify-center text-white text-xs font-bold [font-family:'Inter',Helvetica,sans-serif]">Sign In</button>
            </div>

            <p class="m-0 text-center pt-[26px] pb-[26px] text-stone-300 text-[8px] font-bold tracking-[0.5px] [font-family:'Inter',Helvetica,sans-serif]">
                Don&rsquo;t have an account?
                <a href="{{ route('register') }}" class="text-red-500 no-underline transition-colors hover:text-red-400">Sign Up</a>
            </p>
        </form>
    </div>
</div>
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((toggleButton) => {
        const passwordInput = toggleButton.closest('.relative').querySelector('input');
        toggleButton.addEventListener('click', () => {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            toggleButton.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            toggleButton.setAttribute('aria-pressed', String(show));
            toggleButton.querySelector('.eye-open').classList.toggle('hidden', show);
            toggleButton.querySelector('.eye-off').classList.toggle('hidden', !show);
        });
    });
</script>
@endsection
