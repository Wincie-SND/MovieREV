<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<meta charset="utf-8" />
<link rel="stylesheet" href="globals.css">
<link rel="stylesheet" href="styleguide.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&family=Roboto:wght@500&display=swap" rel="stylesheet">
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
<style>
/*
 * Interactive chrome for the Figma-exported pills (sidebar nav + Get Started).
 * Tailwind runs with preflight off, so a raw <button> would keep the browser UA
 * border, padding and default cursor. These rules keep the pills pixel-identical
 * while making them behave like real controls. Hover/active use filter instead
 * of transform because each label and icon is a sibling element that cannot
 * move along with the pill.
 */
.design-btn {
    appearance: none;
    border: 0;
    padding: 0;
    cursor: pointer;
    transition: filter 0.15s ease, background-color 0.25s ease;
}

.design-btn:hover {
    filter: brightness(0.95);
}

.design-btn:active {
    filter: brightness(0.85);
}

.design-btn:focus-visible {
    outline: 2px solid #a68dd9;
    outline-offset: 3px;
}

/* The search field kills the UA focus ring with `outline-none`; give keyboard
   users it back without changing the mouse-focus appearance. */
.design-input:focus-visible {
    outline: 2px solid #a68dd9;
    outline-offset: 3px;
}
</style>
</head>
<body>
<div class="bg-white overflow-hidden border-0 border-none w-full min-w-[1920px] h-[1080px] relative" ><div class="absolute top-0 left-0 w-[1920px] h-[1080px] [background:linear-gradient(180deg,rgba(31,20,47,1)_30%,rgba(73,46,110,1)_100%)]" ></div>
<button type="button" id="nav-pill-reviews" aria-label="Reviews" class="design-btn absolute top-[354px] left-[13.5px] w-[256.5px] h-[46.5px] bg-[#d9d9d9] rounded-[16.5px]" ></button>
<div id="nav-label-reviews" class="pointer-events-none transition-colors absolute top-[327px] left-[67.5px] w-[129px] [font-family:'Roboto-Medium',Helvetica] font-medium text-[#1e1e1e] text-[22.5px] tracking-[6.98px] leading-[102px] whitespace-nowrap" >Reviews</div>
<img class="absolute top-0 left-0 w-[150px] h-[148.5px] object-contain" src="img/1449066-1.png" />
<div class="absolute top-[148.5px] left-[282px] w-[1702.5px] h-[987px] bg-[#241538] rounded-[37.5px] border border-solid border-[#3f2860]" ></div>
<img class="left-[1662px] absolute top-[642px] w-[214.5px] h-[321px] object-cover" src="img/image-2.png" />
<img class="left-[1398px] absolute top-[642px] w-[214.5px] h-[321px] object-cover" src="img/image-3.png" />
<img class="left-[1134px] absolute top-[642px] w-[214.5px] h-[321px] object-cover" src="img/image-5.png" />
<img class="left-[870px] absolute top-[642px] w-[214.5px] h-[321px] object-cover" src="img/image-4.png" />
<img class="left-[606px] absolute top-[642px] w-[214.5px] h-[321px] object-cover" src="img/image-6.png" />
<div class="absolute top-[610.5px] left-[342px] w-[1542px] h-px bg-[#d9d9d9]" ></div>
<div class="absolute top-[540px] left-[342px] [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[22.5px] tracking-[6.98px] leading-[102px] whitespace-nowrap" >Trending</div>
<div class="flex w-[291px] h-[51px] items-center gap-[var(--size-space-300)] pt-[var(--size-space-450)] pr-[var(--size-space-600)] pb-[var(--size-space-450)] pl-[var(--size-space-600)] absolute top-[490.5px] left-[340.5px] bg-color-background-default-default rounded-[var(--size-radius-full)] overflow-hidden border border-solid border-color-border-default-default" ><input type="text" name="search" placeholder="Find a movie..." aria-label="Find a movie" class="design-input relative flex-1 min-w-0 appearance-none bg-transparent border-0 p-0 outline-none text-[#1e1e1e] placeholder:text-color-text-default-tertiary font-single-line-body-base font-[number:var(--single-line-body-base-font-weight)] text-[length:var(--single-line-body-base-font-size)] tracking-[var(--single-line-body-base-letter-spacing)] leading-[var(--single-line-body-base-line-height)] [font-style:var(--single-line-body-base-font-style)]" />
<img class="relative w-6 h-6" src="img/search.svg" /></div>
<div class="absolute top-[168px] left-[886.5px] w-[493.5px] [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[54px] text-center tracking-[16.74px] leading-[102px]" >Welcome!</div>
<button type="button" id="cta-hero" aria-label="Get Started" class="design-btn absolute top-[273px] left-[832.5px] w-[603px] h-[127.5px] bg-[#6450a1] rounded-[34.5px]" ></button>
<button type="button" id="nav-pill-home" aria-label="Home" aria-current="page" class="design-btn absolute top-[178.5px] left-[13.5px] w-[256.5px] h-[46.5px] bg-[#727272] rounded-[16.5px]" ></button>
<button type="button" id="nav-pill-movies" aria-label="Movies" class="design-btn absolute top-[237px] left-[13.5px] w-[256.5px] h-[46.5px] bg-[#d9d9d9] rounded-[16.5px]" ></button>
<div id="nav-label-movies" class="pointer-events-none transition-colors absolute top-[210px] left-[67.5px] w-[129px] [font-family:'Roboto-Medium',Helvetica] font-medium text-[#1e1e1e] text-[22.5px] tracking-[6.98px] leading-[102px] whitespace-nowrap" >Movies</div>
<button type="button" id="nav-pill-lists" aria-label="Lists" class="design-btn absolute top-[295.5px] left-[13.5px] w-[256.5px] h-[46.5px] bg-[#d9d9d9] rounded-[16.5px]" ></button>
<div id="nav-label-lists" class="pointer-events-none transition-colors absolute top-[268.5px] left-[67.5px] w-[102px] [font-family:'Roboto-Medium',Helvetica] font-medium text-[#1e1e1e] text-[22.5px] tracking-[6.98px] leading-[102px] whitespace-nowrap" >Lists</div>
<div id="nav-label-home" class="pointer-events-none transition-colors absolute top-[151.5px] left-[67.5px] w-[82.5px] [font-family:'Roboto-Medium',Helvetica] font-medium text-[#d9d9d9] text-[22.5px] tracking-[6.98px] leading-[102px] whitespace-nowrap" >Home</div>
<img class="pointer-events-none absolute top-[190.5px] left-[31.5px] w-6 h-6" src="img/home.svg" />
<img class="pointer-events-none absolute top-[307.5px] left-[31.5px] w-6 h-6" src="img/pen-tool.svg" />
<img class="pointer-events-none absolute top-[249px] left-[31.5px] w-6 h-6" src="img/monitor.svg" />
<img class="pointer-events-none absolute top-[366px] left-[31.5px] w-6 h-6" src="img/user.svg" />
<button type="button" id="cta-top" aria-label="Get Started!" class="design-btn absolute top-[43.5px] left-[1630.5px] w-[264px] h-[46.5px] bg-[#d7d7d7] rounded-[16.5px]" ></button>
<div class="pointer-events-none absolute top-[16.5px] left-[1683px] w-[159px] [font-family:'Roboto-Medium',Helvetica] font-medium text-black text-[18px] tracking-[5.58px] leading-[102px] whitespace-nowrap" >Get Started!</div>
<div class="absolute top-[16.5px] left-[1488px] [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[30px] tracking-[3px] leading-[102px] whitespace-nowrap" >Sign In</div>
<div class="pointer-events-none absolute top-[292.5px] left-[936px] w-[396px] h-[87px] flex items-center justify-center [font-family:'Poppins-Bold',Helvetica] font-bold text-white text-[60px] text-center tracking-[0] leading-[86.4px]" >Get Started</div>
<img class="left-[342px] absolute top-[642px] w-[214.5px] h-[321px] object-cover" src="img/image-8.png" />
<div class="absolute top-[31.5px] left-[169.5px] [font-family:'Roboto-Medium',Helvetica] font-medium text-white text-[54px] tracking-[16.74px] leading-[102px] whitespace-nowrap" >MovieREV</div></div>
<script>
    /*
     * The Figma export draws each sidebar item as three separate absolute
     * elements (pill, icon, label) with no shared wrapper, so the pill is the
     * click target and the icon/label sit above it with pointer-events-none.
     * There are no other pages to route to yet, so a click moves the selected
     * treatment (dark pill, light label) instead.
     */
    (function () {
        var items = ['home', 'movies', 'lists', 'reviews'];

        function select(key) {
            items.forEach(function (name) {
                var active = name === key;
                var pill = document.getElementById('nav-pill-' + name);
                var label = document.getElementById('nav-label-' + name);

                pill.style.backgroundColor = active ? '#727272' : '#d9d9d9';
                label.style.color = active ? '#d9d9d9' : '#1e1e1e';

                if (active) {
                    pill.setAttribute('aria-current', 'page');
                } else {
                    pill.removeAttribute('aria-current');
                }
            });
        }

        items.forEach(function (name) {
            document.getElementById('nav-pill-' + name).addEventListener('click', function () {
                select(name);
            });
        });
    })();
</script>
</body>
</html>