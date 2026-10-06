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
                    placeholder="bartoletti.maxie or you@example.com"
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
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="w-full h-[54px] px-[18px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                />
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
@endsection
