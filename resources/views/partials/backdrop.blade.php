{{--
    Cinematic page backdrop, shared by the dashboard and the movie detail page.

    Film grain is declared as plain CSS rather than a Tailwind arbitrary value:
    the SVG data URI is full of characters (`%`, `'`, `#`) that Tailwind's
    arbitrary-value parser would mangle.
--}}
<style>
    .dashboard-grain {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='grain'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.82' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23grain)'/%3E%3C/svg%3E");
    }
</style>

{{-- The same tilted still as the auth pages, drained to a texture and lit by two
     coloured spotlights, then reined in by a vignette and grain. `fixed` +
     `pointer-events-none` keeps it off the scroll and out of the way of clicks;
     it is first in tree order, so every positioned sibling below (`header`,
     `main`) paints on top of it. --}}
<div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
    <img src="{{ asset('img/forms_background.jpg') }}" alt=""
         class="absolute left-1/2 top-1/2 h-[150%] w-[150%] max-w-none -translate-x-1/2 -translate-y-1/2 -rotate-3 object-cover opacity-[0.12]" />
    <div class="absolute inset-0 [background:radial-gradient(58%_42%_at_15%_0%,rgba(239,68,68,0.22),transparent_62%),radial-gradient(52%_46%_at_100%_6%,rgba(168,85,247,0.18),transparent_64%)]"></div>
    <div class="absolute inset-0 [background:radial-gradient(ellipse_at_center,rgba(0,0,0,0)_36%,rgba(0,0,0,0.55)_76%,rgba(0,0,0,0.92)_100%)]"></div>
    <div class="dashboard-grain absolute inset-0 opacity-[0.05] mix-blend-overlay"></div>
</div>
