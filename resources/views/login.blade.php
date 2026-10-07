@extends('layouts.app')

@section('title', 'Sign In — MovieREV')

@section('content')
<div class="min-h-screen flex items-center justify-center px-[24px] py-[46.5px]">
    <div class="w-full max-w-[603px] bg-[#241538] border border-solid border-[#3f2860] rounded-[37.5px] p-[46.5px]">

        {{-- Logo links back home --}}
        <a href="{{ route('home') }}" class="flex items-center gap-[13.5px] no-underline mb-[27px]">
            <img src="{{ asset('img/1449066-1.png') }}" alt="" class="w-[75px] h-[74.25px] object-contain"/>
            <span class="[font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[30px] tracking-[9px] whitespace-nowrap">MovieREV</span>
        </a>

        <h1 class="m-0 mb-[9px] [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[36px] tracking-[9px] leading-[1.4]" >Sign In</h1>
        <p class="m-0 mb-[27px] [font-family:'Roboto',Helvetica] text-[#b4b4b4] text-[18px]" >Welcome back — pick up where you left off.</p>

        <form method="POST" action="{{ route('login.attempt') }}" class="flex flex-col gap-[18px]" >
            @csrf

            <div>
                <label for="identifier" class="block [font-family:'Roboto',Helvetica] font-medium text-[#d9d9d9] text-[18px] tracking-[3px] mb-[9px]" >Username or Email</label>
                <input
                    type="text"
                    id="identifier"
                    name="identifier"
                    value="{{ old('identifier') }}"
                    placeholder="randomuser or email@example.com"
                    autocomplete="username"
                    autofocus
                    class="w-full h-[54px] px-[18px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                />
                @error('identifier')
                    <p class="m-0 mt-[6px] [font-family:'Roboto',Helvetica] text-[#ff9b9b] text-[15px]" >{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block [font-family:'Roboto',Helvetica] font-medium text-[#d9d9d9] text-[18px] tracking-[3px] mb-[9px]" >Password</label>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="w-full h-[54px] pl-[18px] pr-[56px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                    />
                    <button
                        type="button"
                        id="toggle-password"
                        aria-label="Show password"
                        aria-pressed="false"
                        class="absolute right-[12px] top-[13px] w-[28px] h-[28px] flex items-center justify-center bg-transparent border-0 p-0 cursor-pointer text-[#d9d9d9] hover:text-white"
                    >
                        <svg class="eye-open w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg class="eye-off hidden w-[22px] h-[22px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="m-0 mt-[6px] [font-family:'Roboto',Helvetica] text-[#ff9b9b] text-[15px]" >{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-[9px] [font-family:'Roboto',Helvetica] text-[#d9d9d9] text-[18px] cursor-pointer" >
                <input type="checkbox" name="remember" value="1" class="w-[18px] h-[18px] accent-[#6450a1]" />
                Remember me
            </label>

            <button type="submit" class="mt-[9px] h-[54px] bg-[#6450a1] hover:bg-[#7a5fc0] border-0 rounded-[34.5px] cursor-pointer [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[22.5px] tracking-[6.98px]" >Sign In</button>
        </form>

        <p class="m-0 mt-[27px] [font-family:'Roboto',Helvetica] text-[#b4b4b4] text-[18px]" >
            New here?
            <a href="{{ route('register') }}" class="text-white font-medium no-underline hover:underline" >Create an account</a>
        </p>
    </div>
</div>
<script>
    const toggleButton = document.getElementById('toggle-password');
    if (toggleButton) {
        const passwordInput = document.getElementById('password');
        toggleButton.addEventListener('click', () => {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            toggleButton.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            toggleButton.setAttribute('aria-pressed', String(show));
            toggleButton.querySelector('.eye-open').classList.toggle('hidden', show);
            toggleButton.querySelector('.eye-off').classList.toggle('hidden', !show);
        });
    }
</script>
@endsection
