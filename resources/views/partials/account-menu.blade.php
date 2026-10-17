<div class="profile-dropdown relative" data-profile-dropdown>
    <button type="button" class="flex cursor-pointer items-center gap-2 border-0 bg-transparent p-0 text-xs font-bold uppercase tracking-[0.12em] text-stone-200 transition-colors hover:text-red-300" aria-label="Open account menu" aria-expanded="false" aria-controls="account-menu-panel" data-profile-menu-toggle>
        <span aria-hidden="true" class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/15 bg-gradient-to-br from-fuchsia-900 to-zinc-900 font-bebas text-base sm:h-10 sm:w-10 sm:text-lg">{{ mb_strtoupper(mb_substr(Auth::user()->username, 0, 1)) }}</span>
        <span class="hidden max-w-[120px] truncate md:inline">{{ Auth::user()->username }}</span>
        <svg class="profile-dropdown-arrow h-3 w-3 text-stone-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
    </button>
    <div id="account-menu-panel" class="profile-dropdown-menu absolute right-0 top-full z-50 w-52 pt-2">
        <nav class="overflow-hidden rounded-xl border border-white/10 bg-[#160b1b] py-1 shadow-xl" aria-label="Account">
            @if (Auth::user()->usertype === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-xs font-semibold text-stone-200 transition-colors hover:bg-white/5 hover:text-red-300">Admin Dashboard</a>
            @endif
            <a href="{{ route('users.show', Auth::user()->username) }}" class="block px-4 py-2.5 text-xs font-semibold text-stone-200 transition-colors hover:bg-white/5 hover:text-red-300">Profile</a>
            <a href="{{ route('settings') }}" class="block px-4 py-2.5 text-xs font-semibold text-stone-200 transition-colors hover:bg-white/5 hover:text-red-300">Settings</a>
        </nav>
    </div>
</div>
