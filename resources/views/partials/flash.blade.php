{{--
    Green "bubble" confirmation banner, shown for any flashed `status` message
    (currently: account creation, see AuthController::register).

    `fixed` keeps it clear of both layouts — the welcome page is a fixed
    1920x1080 canvas and the auth pages centre a 1201x670 frame — so it is
    pinned to the viewport rather than any of those boxes. The animation sits
    on the inner bubble so it never fights the outer `-translate-x-1/2`.
--}}
@if (session('status'))
    <div id="flash-banner" role="status" aria-live="polite"
         class="fixed left-1/2 top-[28px] z-50 -translate-x-1/2">
        <div class="flex items-center gap-[10px] rounded-full bg-green-500 px-[22px] py-[11px] text-white shadow-[0_12px_32px_rgba(0,0,0,0.45)] [font-family:'Inter','Roboto',Helvetica,sans-serif] text-[13px] font-bold leading-none"
            style="animation: movieRevFlashIn 450ms cubic-bezier(.22,1,.36,1) both">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    </div>

    <style>
        @keyframes movieRevFlashIn {
            from { opacity: 0; transform: translateY(-14px) scale(.96); }
            to   { opacity: 1; transform: none; }
        }
    </style>

    <script>
        // Auto-dismiss after a few seconds, then drop it from the DOM.
        (function () {
            const banner = document.getElementById('flash-banner');
            if (!banner) return;

            setTimeout(function () {
                banner.style.transition = 'opacity 400ms ease, transform 400ms ease';
                banner.style.opacity = '0';
                banner.style.transform = 'translate(-50%, -12px)';
                setTimeout(function () { banner.remove(); }, 420);
            }, 5000);
        })();
    </script>
@endif
