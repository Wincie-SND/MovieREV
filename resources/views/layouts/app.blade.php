<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>@yield('title', 'MovieREV')</title>
    <link rel="stylesheet" href="{{ asset('globals.css') }}">
    <link rel="stylesheet" href="{{ asset('styleguide.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
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

          },},
      },
      plugins: [],
    }
    </script>
    {{--
        globals.css pins html/body to overflow:hidden for the fixed 1920x1080
        welcome canvas. Auth pages are normal scrolling pages, so undo just
        that locally (inline style beats globals.css's element selectors).
    --}}
    <style>
        html, body {
            overflow: auto;
            height: auto;
        }
    </style>
</head>
<body class="min-h-screen [background:linear-gradient(180deg,rgba(31,20,47,1)_30%,rgba(73,46,110,1)_100%)]">
    @yield('content')
</body>
</html>
