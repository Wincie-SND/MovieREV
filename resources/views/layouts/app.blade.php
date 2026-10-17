<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    @if (Auth::check() && session('session_only_login'))
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            (() => {
                const tabKey = 'movierev-session-only-tab';
                const logoutUrl = @json(route('logout'));
                const dashboardUrl = @json(route('home'));
                const csrfToken = @json(csrf_token());

                const endSession = () => navigator.sendBeacon(logoutUrl, new URLSearchParams({ _token: csrfToken }));

                if (sessionStorage.getItem(tabKey) !== 'active') {
                    document.documentElement.style.visibility = 'hidden';
                    const body = new URLSearchParams({ _token: csrfToken });

                    fetch(logoutUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString(),
                        keepalive: true,
                    }).finally(() => window.location.replace(dashboardUrl));
                }

                document.addEventListener('submit', (event) => {
                    const form = event.target;
                    if (form instanceof HTMLFormElement && new URL(form.action, location.href).origin !== location.origin && form.target !== '_blank') {
                        sessionStorage.removeItem(tabKey);
                        endSession();
                    }
                }, true);

                document.addEventListener('click', (event) => {
                    const link = event.target.closest('a[href]');
                    if (!link || (link.target && link.target !== '_self')) return;

                    try {
                        if (new URL(link.href, location.href).origin !== location.origin) {
                            sessionStorage.removeItem(tabKey);
                            endSession();
                        }
                    } catch (_) {}
                }, true);
            })();
        </script>
    @endif
    <title>@yield('title', 'MovieREV')</title>
    <link rel="icon" type="image/png" href="{{ asset('img/1449066-1.png') }}">
    <link rel="stylesheet" href="{{ asset('globals.css') }}">
    <link rel="stylesheet" href="{{ asset('styleguide.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Roboto:wght@400;500&family=Playfair+Display:wght@700&family=Inter:wght@400;700&family=Bebas+Neue&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    /** @type {import('tailwindcss').Config} */
    tailwind.config = {

      corePlugins: {
        preflight: false,
      },
      theme: {
        extend: {

          colors:{
            "color-background-default-default": "var(--color-background-default-default)",
          "color-border-default-default": "var(--color-border-default-default)",
          "color-primitives-gray-300": "var(--color-primitives-gray-300)",
          "color-primitives-gray-400": "var(--color-primitives-gray-400)",
          "color-primitives-white-1000": "var(--color-primitives-white-1000)",
          "color-text-default-tertiary": "var(--color-text-default-tertiary)",

          },fontFamily:{
            "single-line-body-base": "var(--single-line-body-base-font-family)",
            "bebas": ['"Bebas Neue"', '"Arial Narrow"', 'sans-serif'],

          },},
      },
      plugins: [],
    }
    </script>
    {{--
        globals.css pins html/body to overflow:hidden for the fixed 1920x1080
        welcome canvas. Auth pages are normal scrolling pages, so undo just
        that locally (inline style beats globals.css's element selectors).

        `preflight: false` means Tailwind never applies its `a { text-decoration:
        inherit }` base rule, so every anchor keeps the browser's default
        underline. Kill it globally here. `!important` is deliberate: it makes
        the reset authoritative so no later utility can reintroduce an
        underline by accident.
    --}}
    <style>
        html, body {
            overflow: auto;
            height: auto;
        }

        a,
        button,
        [role="button"] {
            text-decoration: none !important;
        }

        .profile-dropdown-menu {
            opacity: 0;
            pointer-events: none;
            transform: translateY(-0.4rem) scale(0.98);
            transform-origin: top right;
            transition: opacity 160ms ease, transform 160ms ease, visibility 0s linear 160ms;
            visibility: hidden;
        }

        .profile-dropdown-arrow {
            transform-box: fill-box;
            transform-origin: center;
            transition: transform 160ms ease;
        }

        .profile-dropdown.is-open > .profile-dropdown-menu,
        .profile-dropdown.is-hovered > .profile-dropdown-menu,
        .profile-dropdown:hover > .profile-dropdown-menu,
        .profile-dropdown:focus-within > .profile-dropdown-menu {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
            transition: opacity 160ms ease, transform 160ms ease, visibility 0s;
            visibility: visible;
        }

        .profile-dropdown.is-open > [data-profile-menu-toggle] .profile-dropdown-arrow,
        .profile-dropdown.is-hovered > [data-profile-menu-toggle] .profile-dropdown-arrow,
        .profile-dropdown:hover > [data-profile-menu-toggle] .profile-dropdown-arrow,
        .profile-dropdown:focus-within > [data-profile-menu-toggle] .profile-dropdown-arrow {
            transform: rotate(180deg);
        }

        @media (prefers-reduced-motion: reduce) {
            .profile-dropdown-menu,
            .profile-dropdown-arrow {
                transition: none;
            }
        }

        /*
         * `input[type="search"]` keeps the UA's own searchfield chrome when
         * preflight is off: a `2px inset` border plus decorative widgets. Inside
         * a rounded, already-bordered pill that border paints as a rectangular
         * outline overlapping the pill, so strip the appearance and hide the
         * decorations here.
         *
         * The border itself is left alone on purpose. This selector is an
         * element + attribute match, so it outranks a utility class, and a
         * blanket `border-width: 0` would erase the intended `border` on the
         * admin search field. Borderless search inputs opt in with `border-0`.
         */
        input[type="search"] {
            -webkit-appearance: none;
            appearance: none;
        }

        input:autofill {
            color: #fff !important;
            caret-color: #fff;
            transition: background-color 9999s ease-out 0s;
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-text-fill-color: #fff !important;
            color: #fff !important;
            caret-color: #fff;
            transition: background-color 9999s ease-out 0s;
        }

        input:-moz-autofill {
            color: #fff !important;
            caret-color: #fff;
            transition: background-color 9999s ease-out 0s;
        }

        input[type="search"]::-webkit-search-decoration,
        input[type="search"]::-webkit-search-cancel-button,
        input[type="search"]::-webkit-search-results-button,
        input[type="search"]::-webkit-search-results-decoration,
        input[type="search"]::-ms-clear,
        input[type="search"]::-ms-reveal {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }
    </style>
</head>
<body class="min-h-screen [background:linear-gradient(180deg,rgba(31,20,47,1)_30%,rgba(73,46,110,1)_100%)]">
    @include('partials.flash')
    @yield('content')
    <script>
        document.querySelectorAll('[data-profile-dropdown]').forEach((dropdown) => {
            const toggle = dropdown.querySelector('[data-profile-menu-toggle]');

            const syncExpanded = () => {
                toggle.setAttribute('aria-expanded', String(
                    dropdown.classList.contains('is-open') || dropdown.classList.contains('is-hovered') || dropdown.matches(':hover') || dropdown.matches(':focus-within')
                ));
            };

            dropdown.addEventListener('mouseenter', () => {
                dropdown.classList.add('is-hovered');
                syncExpanded();
            });

            dropdown.addEventListener('mouseleave', () => {
                dropdown.classList.remove('is-hovered');
                syncExpanded();
            });

            toggle.addEventListener('click', () => {
                dropdown.classList.toggle('is-open');
                syncExpanded();
            });

            document.addEventListener('click', (event) => {
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove('is-open');
                    syncExpanded();
                }
            });

            dropdown.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    dropdown.classList.remove('is-open');
                    syncExpanded();
                    toggle.focus();
                }
            });
        });
    </script>
</body>
</html>
