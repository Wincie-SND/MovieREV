<div class="grid gap-4">
    <div>
        <label for="title" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-stone-300">Title</label>
        <input id="title" name="title" type="text" value="{{ old('title', $movie->title) }}" required maxlength="150" placeholder="Untitled feature" class="h-10 w-full rounded-lg border border-white/10 bg-[#2A0A2E] px-4 text-sm text-white outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500" />
        @error('title')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="release_year" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-stone-300">Release year</label>
            <input id="release_year" name="release_year" type="number" min="1888" max="{{ date('Y') + 2 }}" value="{{ old('release_year', $movie->release_year) }}" placeholder="2026" class="h-10 w-full rounded-lg border border-white/10 bg-[#2A0A2E] px-4 text-sm text-white outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500" />
            @error('release_year')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="genre" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-stone-300">Genre</label>
            <input id="genre" name="genre" type="text" value="{{ old('genre', $movie->genre) }}" maxlength="50" placeholder="Drama, Western, Sci-Fi" class="h-10 w-full rounded-lg border border-white/10 bg-[#2A0A2E] px-4 text-sm text-white outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500" />
            @error('genre')
                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="description" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-stone-300">Description</label>
        <textarea id="description" name="description" rows="4" maxlength="5000" placeholder="One or two sentences shown on the dashboard hero." class="w-full rounded-lg border border-white/10 bg-[#2A0A2E] px-4 py-3 text-sm leading-relaxed text-white outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500">{{ old('description', $movie->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="poster_url" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.18em] text-stone-300">Poster URL or image path</label>
        <input id="poster_url" name="poster_url" type="text" value="{{ old('poster_url', $movie->poster_url) }}" maxlength="255" placeholder=https://… or /img/example.jpg" class="h-10 w-full rounded-lg border border-white/10 bg-[#2A0A2E] px-4 text-sm text-white outline-none placeholder:text-stone-500 focus:ring-1 focus:ring-red-500" />
        <p class="mt-1 text-xs text-stone-400">Leave blank to display the automatic placeholder. Only secure links and site-relative paths are displayed.</p>
        @error('poster_url')
            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>
