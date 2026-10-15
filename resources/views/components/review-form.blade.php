{{--
    The member review form, shared by "write" and "edit".

    `$review` prefills the fields (and, with `$action` pointing at
    `reviews.update`, makes it an edit). Old input wins so a failed validation
    round-trip keeps what was typed.

    The star picker is five real radio inputs reversed into visual order; a
    scoped rule paints every star up to the checked one. `<style>` mirrors the
    precedent in partials/flash.blade.php, since the site has no CSS build step.
--}}
@props([
    'action',
    'method' => 'POST',
    'review' => null,
    'submitLabel' => 'Submit review',
    'cancelUrl' => null,
])

@php
    $selectedRating = (int) old('rating', $review?->rating ?? 0);
    $titleValue = old('title', $review?->title ?? '');
    $bodyValue = old('body', $review?->body ?? '');
@endphp

@once
    <style>
        .review-star-picker input:checked ~ label { color: #ef4444; }
    </style>
@endonce

<form method="POST" action="{{ $action }}" class="m-0">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-4">
        <fieldset class="m-0 min-w-0 border-0 p-0">
            <legend class="mb-1 text-xs font-bold uppercase tracking-[0.14em] text-stone-300">Your rating</legend>
            <div class="review-star-picker flex flex-row-reverse justify-end gap-1" role="radiogroup" aria-label="Rating from 1 to 5 stars">
                @for ($star = 5; $star >= 1; $star--)
                    <input id="rating-{{ $star }}" type="radio" name="rating" value="{{ $star }}" class="peer sr-only" @checked($selectedRating === $star) />
                    <label for="rating-{{ $star }}" title="{{ $star }} {{ \Illuminate\Support\Str::plural('star', $star) }}"
                           class="cursor-pointer text-3xl leading-none text-stone-600 transition-colors hover:text-red-400">
                        <span class="sr-only">{{ $star }} {{ \Illuminate\Support\Str::plural('star', $star) }}</span>
                        <span aria-hidden="true">★</span>
                    </label>
                @endfor
            </div>
            @error('rating')
                <p class="mt-1 text-xs font-bold text-red-400">{{ $message }}</p>
            @enderror
        </fieldset>

        <div>
            <label for="review-title-{{ $review?->id ?? 'new' }}" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-stone-300">
                Review title <span class="font-normal normal-case text-stone-500">(optional)</span>
            </label>
            <input id="review-title-{{ $review?->id ?? 'new' }}" name="title" type="text" maxlength="120"
                   value="{{ $titleValue }}" placeholder="Sum it up in a line"
                   class="w-full rounded-lg border border-white/15 bg-[#150017]/85 px-3 py-2 text-sm text-white outline-none placeholder:text-stone-500 focus:border-red-500/60" />
            @error('title')
                <p class="mt-1 text-xs font-bold text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="review-body-{{ $review?->id ?? 'new' }}" class="mb-1 block text-xs font-bold uppercase tracking-[0.14em] text-stone-300">Your review</label>
            <textarea id="review-body-{{ $review?->id ?? 'new' }}" name="body" rows="5" maxlength="2000"
                      placeholder="What did you think? No spoilers, please."
                      class="w-full rounded-lg border border-white/15 bg-[#150017]/85 px-3 py-2 text-sm leading-relaxed text-white outline-none placeholder:text-stone-500 focus:border-red-500/60">{{ $bodyValue }}</textarea>
            @error('body')
                <p class="mt-1 text-xs font-bold text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="cursor-pointer rounded-full border-0 bg-red-500 px-6 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600">
                {{ $submitLabel }}
            </button>
            @if ($cancelUrl)
                <a href="{{ $cancelUrl }}" class="no-underline text-xs font-bold uppercase tracking-[0.14em] text-stone-400 transition-colors hover:text-red-400">Cancel</a>
            @endif
        </div>
    </div>
</form>
