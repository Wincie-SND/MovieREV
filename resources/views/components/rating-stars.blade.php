@props(['value' => 0])

@php
    $rating = max(0, min(5, (int) round((float) $value)));
@endphp

<p class="flex items-center gap-[3px] text-sm leading-none" role="img" aria-label="{{ $rating }} out of 5 stars">
    @for ($star = 1; $star <= 5; $star++)
        <span aria-hidden="true" class="{{ $star <= $rating ? 'text-red-500' : 'text-stone-700' }}">★</span>
    @endfor
</p>
