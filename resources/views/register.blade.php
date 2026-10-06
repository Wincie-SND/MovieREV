@extends('layouts.app')

@section('title', 'Create Account — MovieREV')

@section('content')
<div class="min-h-screen flex items-center justify-center px-[24px] py-[46.5px]">
    <div class="w-full max-w-[603px] bg-[#241538] border border-solid border-[#3f2860] rounded-[37.5px] p-[46.5px]">

        {{-- Logo links back home --}}
        <a href="{{ route('home') }}" class="flex items-center gap-[13.5px] no-underline mb-[27px]">
            <img src="{{ asset('img/1449066-1.png') }}" alt="" class="w-[75px] h-[74.25px] object-contain"/>
            <span class="[font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[30px] tracking-[9px] whitespace-nowrap">MovieREV</span>
        </a>

        <h1 class="m-0 mb-[9px] [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[36px] tracking-[9px] leading-[1.4]" >Create Account</h1>
        <p class="m-0 mb-[27px] [font-family:'Roboto',Helvetica] text-[#b4b4b4] text-[18px]" >Join MovieREV to rate films and build lists.</p>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-[18px]" >
            @csrf

            <div>
                <label for="name" class="block [font-family:'Roboto',Helvetica] font-medium text-[#d9d9d9] text-[18px] tracking-[3px] mb-[9px]" >Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Your name"
                    autocomplete="name"
                    autofocus
                    class="w-full h-[54px] px-[18px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                />
                @error('name')
                    <p class="m-0 mt-[6px] [font-family:'Roboto',Helvetica] text-[#ff9b9b] text-[15px]" >{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="username" class="block [font-family:'Roboto',Helvetica] font-medium text-[#d9d9d9] text-[18px] tracking-[3px] mb-[9px]" >Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="bartoletti.maxie"
                    autocomplete="username"
                    class="w-full h-[54px] px-[18px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                />
                @error('username')
                    <p class="m-0 mt-[6px] [font-family:'Roboto',Helvetica] text-[#ff9b9b] text-[15px]" >{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block [font-family:'Roboto',Helvetica] font-medium text-[#d9d9d9] text-[18px] tracking-[3px] mb-[9px]" >Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    autocomplete="email"
                    class="w-full h-[54px] px-[18px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                />
                @error('email')
                    <p class="m-0 mt-[6px] [font-family:'Roboto',Helvetica] text-[#ff9b9b] text-[15px]" >{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block [font-family:'Roboto',Helvetica] font-medium text-[#d9d9d9] text-[18px] tracking-[3px] mb-[9px]" >Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="At least 8 characters"
                    autocomplete="new-password"
                    class="w-full h-[54px] px-[18px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                />
                @error('password')
                    <p class="m-0 mt-[6px] [font-family:'Roboto',Helvetica] text-[#ff9b9b] text-[15px]" >{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block [font-family:'Roboto',Helvetica] font-medium text-[#d9d9d9] text-[18px] tracking-[3px] mb-[9px]" >Confirm Password</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Repeat your password"
                    autocomplete="new-password"
                    class="w-full h-[54px] px-[18px] bg-[#1f142f] border border-solid border-[#3f2860] rounded-[16.5px] [font-family:'Roboto',Helvetica] text-white text-[22.5px] outline-none focus:border-[#6450a1]"
                />
            </div>

            <button type="submit" class="mt-[9px] h-[54px] bg-[#6450a1] hover:bg-[#7a5fc0] border-0 rounded-[34.5px] cursor-pointer [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[22.5px] tracking-[6.98px]" >Create Account</button>
        </form>

        <p class="m-0 mt-[27px] [font-family:'Roboto',Helvetica] text-[#b4b4b4] text-[18px]" >
            Already have an account?
            <a href="{{ route('login') }}" class="text-white font-medium no-underline hover:underline" >Sign in</a>
        </p>
    </div>
</div>
@endsection
