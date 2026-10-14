@extends('layouts.app')

@section('title', 'Sign In — MovieREV')

@section('content')
<div class="min-h-screen w-full relative flex [background:linear-gradient(270deg,#000000_10%,#4a044e_41%,#000000_85%)]">

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
            all in
            <span class="block pl-[100px] text-red-700">one place.</span>
        </h1>

        {{-- Sign-in card --}}
        <form method="POST" action="{{ route('login.attempt') }}"
              class="absolute left-[633px] top-[76px] w-[497px] min-h-[496px] bg-zinc-950 flex flex-col">
            @csrf

            <h2 class="m-0 pt-[35px] text-center text-white text-4xl font-bold leading-[1.15] [font-family:'Playfair_Display',Georgia,serif]">Sign In</h2>

            <div class="flex flex-col pl-[117px] pt-[38px]">
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
                        class="w-72 h-6 bg-stone-800 border-0 px-[12px] text-white text-xs [font-family:'Inter',Helvetica,sans-serif] outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500"
                    />
                    @error('identifier')
                        <p class="m-0 mt-[3px] text-red-400 text-[10px] [font-family:'Inter',Helvetica,sans-serif]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mt-[15px]">
                    <label for="password" class="block mb-[1px] text-stone-300 text-[8px] font-bold uppercase leading-[14px] tracking-[1px] [font-family:'Inter',Helvetica,sans-serif]">Password</label>
                    <div class="relative w-72">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            class="w-72 h-6 bg-stone-800 border-0 pl-[12px] pr-[34px] text-white text-xs [font-family:'Inter',Helvetica,sans-serif] outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500"
                        />
                        <button
                            type="button"
                            data-password-toggle
                            aria-label="Show password"
                            aria-pressed="false"
                            class="absolute right-[6px] top-[3px] w-[18px] h-[18px] flex items-center justify-center bg-transparent border-0 p-0 cursor-pointer text-stone-400 hover:text-white"
                        >
                            <svg class="eye-open w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg class="eye-off hidden w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="m-0 mt-[3px] text-red-400 text-[10px] [font-family:'Inter',Helvetica,sans-serif]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me (the controller always remembers; see AuthController) --}}
                <label class="mt-[8px] flex items-center gap-[7px] cursor-pointer text-stone-300 text-[8px] font-bold leading-[14px] tracking-[1px] [font-family:'Inter',Helvetica,sans-serif]">
                    <input type="checkbox" name="remember" value="1" class="m-0 w-3 h-3 accent-red-500"/>
                    Remember me
                </label>

                <button type="submit" class="mt-[12px] w-72 h-10 bg-red-500 hover:bg-red-600 border-0 cursor-pointer flex items-center justify-center text-white text-xs font-bold [font-family:'Inter',Helvetica,sans-serif]">Sign In</button>
            </div>

            <p class="m-0 pl-[158px] pt-[50px] pb-[30px] text-stone-300 text-[8px] font-bold tracking-[0.5px] [font-family:'Inter',Helvetica,sans-serif]">
                Don&rsquo;t have an account?
                <a href="{{ route('register') }}" class="text-red-500 no-underline hover:underline">Sign Up</a>
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
