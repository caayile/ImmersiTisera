<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Imersi') TSU Industry Immersion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script>
        (() => {
            const isPageTransition = sessionStorage.getItem('is-page-transition');
            const navEntry = window.performance && performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
            const isReload = navEntry ? navEntry.type === 'reload' : (window.performance && performance.navigation && performance.navigation.type === 1);
            if (isPageTransition && !isReload) {
                document.documentElement.classList.add('skip-splash');
            }
        })();
    </script>
</head>
<body class="bg-white text-ink" x-data="{ open: false }">
<div id="brand-splash" class="brand-splash" aria-label="Memuat Imersi" role="status">
    <div class="brand-splash__glow"></div>
    <div id="brand-splash-mark" class="brand-splash__mark">
        <img src="{{ asset('images/logo-tsu.svg') }}" alt="TSU" class="brand-splash__logo">
    </div>
    <div id="brand-splash-wordmark" class="brand-splash__wordmark">
        <span>Imersi</span>
        <small>TSU INDUSTRY IMMERSION</small>
    </div>
    <div class="brand-splash__line" aria-hidden="true"><span></span></div>
</div>
@php
    $nav = [
        ['Beranda', 'home'],
        ['Departemen', 'departments.index'],
        ['Berita', 'news.index'],
    ];
    if (auth()->check()) {
        $nav[] = ['Profil', 'profile.public'];
    }
@endphp
<header class="sticky top-0 z-30 border-b border-line bg-white">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold">
            <img id="nav-logo" src="{{ asset('images/logo-tsu.svg') }}" alt="TSU" class="site-logo site-logo--nav">
            <span id="nav-brand-text">Imersi
                <span class="block text-[10px] font-medium uppercase tracking-[0.14em] text-muted">TSU Industry Immersion</span>
            </span>
        </a>
        <nav class="hidden items-center gap-1 text-sm font-medium md:flex">
            @foreach($nav as [$label, $name])
                <a href="{{ route($name) }}" class="rounded-full px-4 py-2 {{ request()->routeIs($name) ? 'bg-primary/15 text-primary-dark' : 'text-ink hover:bg-bg' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-2 text-sm">
            @auth
                <a href="{{ route(auth()->user()->homeRoute()) }}" class="hidden rounded-full px-4 py-2 font-medium text-ink hover:bg-bg sm:inline">Dasbor</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-full border border-ink px-4 py-2 font-medium">Keluar</button>
                </form>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-full border border-ink px-4 py-2 font-medium">+ Pendaftaran</a>
                <a href="{{ route('login') }}" class="rounded-full bg-primary px-4 py-2 font-semibold text-white">Masuk</a>
            @endauth
            <button class="rounded-lg border border-line px-3 py-1 md:hidden" @click="open = !open">Menu</button>
        </div>
    </div>
    <div x-show="open" x-cloak class="border-t border-line bg-white px-5 py-3 text-sm md:hidden">
        @foreach($nav as [$label, $name])
            <a href="{{ route($name) }}" class="block py-2">{{ $label }}</a>
        @endforeach
        @auth
            <a href="{{ route(auth()->user()->homeRoute()) }}" class="block py-2">Dasbor</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="block py-2">Keluar</button></form>
        @else
            <a href="{{ route('register') }}" class="block py-2">Pendaftaran</a>
            <a href="{{ route('login') }}" class="block py-2">Masuk</a>
        @endauth
    </div>
</header>
@if(session('status'))
    <div class="bg-primary/15 px-5 py-3 text-center text-sm text-primary-dark">{{ session('status') }}</div>
@endif
<main>@yield('content')</main>
<footer class="border-t border-line bg-white py-8 text-center text-sm text-muted">Imersi · Tiga Serangkai · TSU Industry Immersion</footer>
<script>
    (() => {
        const splash = document.getElementById('brand-splash');
        const splashMark = document.getElementById('brand-splash-mark');
        const splashWordmark = document.getElementById('brand-splash-wordmark');
        const navLogo = document.getElementById('nav-logo');
        const navBrandText = document.getElementById('nav-brand-text');

        if (!splash) return;

        const isPageTransition = sessionStorage.getItem('is-page-transition');
        const navEntry = window.performance && performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
        const isReload = navEntry ? navEntry.type === 'reload' : (window.performance && performance.navigation && performance.navigation.type === 1);

        // If internal page navigation and NOT a reload -> remove splash immediately!
        if (isPageTransition && !isReload) {
            splash.remove();
            if (navLogo) navLogo.style.opacity = '1';
            if (navBrandText) navBrandText.style.opacity = '1';
            sessionStorage.removeItem('is-page-transition');
            return;
        }

        // First visit or Page Reload -> run TS logo animation & morph
        sessionStorage.removeItem('is-page-transition');
        if (!splashMark) return;

        if (navLogo) navLogo.style.opacity = '0';
        if (navBrandText) navBrandText.style.opacity = '0';

        setTimeout(() => {
            if (navLogo && splashMark && splashWordmark && navBrandText) {
                // 1. Calculate Logo flight delta & scale
                const navLogoRect = navLogo.getBoundingClientRect();
                const splashMarkRect = splashMark.getBoundingClientRect();

                const navLogoCenterX = navLogoRect.left + navLogoRect.width / 2;
                const navLogoCenterY = navLogoRect.top + navLogoRect.height / 2;
                const splashMarkCenterX = splashMarkRect.left + splashMarkRect.width / 2;
                const splashMarkCenterY = splashMarkRect.top + splashMarkRect.height / 2;

                const dxLogo = navLogoCenterX - splashMarkCenterX;
                const dyLogo = navLogoCenterY - splashMarkCenterY;
                const scaleLogo = navLogoRect.width / splashMarkRect.width;

                // 2. Calculate Wordmark (Text) flight delta & scale
                const navTextRect = navBrandText.getBoundingClientRect();
                const splashTextRect = splashWordmark.getBoundingClientRect();

                const navTextCenterX = navTextRect.left + navTextRect.width / 2;
                const navTextCenterY = navTextRect.top + navTextRect.height / 2;
                const splashTextCenterX = splashTextRect.left + splashTextRect.width / 2;
                const splashTextCenterY = splashTextRect.top + splashTextRect.height / 2;

                const dxText = navTextCenterX - splashTextCenterX;
                const dyText = navTextCenterY - splashTextCenterY;
                const scaleText = navTextRect.height / splashTextRect.height;

                // Stop initial animations so transition is smooth
                splashMark.style.animation = 'none';
                splashWordmark.style.animation = 'none';
                void splashMark.offsetWidth;
                void splashWordmark.offsetWidth;

                // Activate morph mode
                splash.classList.add('brand-splash--morphing');

                // Animate Logo
                splashMark.style.transition = 'transform 1.15s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 1.0s cubic-bezier(0.22, 1, 0.36, 1), border-radius 1.0s cubic-bezier(0.22, 1, 0.36, 1), background-color 1.0s cubic-bezier(0.22, 1, 0.36, 1), padding 1.0s cubic-bezier(0.22, 1, 0.36, 1)';
                splashMark.style.transform = `translate3d(${dxLogo}px, ${dyLogo}px, 0) scale(${scaleLogo})`;
                splashMark.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.08)';
                splashMark.style.padding = '0';
                splashMark.style.backgroundColor = 'transparent';
                splashMark.style.borderRadius = '0.55rem';

                const logoImg = splashMark.querySelector('img');
                if (logoImg) {
                    logoImg.style.borderRadius = '0.55rem';
                }

                // Animate Text
                splashWordmark.style.transition = 'transform 1.15s cubic-bezier(0.22, 1, 0.36, 1)';
                splashWordmark.style.transform = `translate3d(${dxText}px, ${dyText}px, 0) scale(${scaleText})`;

                setTimeout(() => {
                    navLogo.style.opacity = '1';
                    navBrandText.style.opacity = '1';
                    splash.remove();
                }, 1150);
            } else {
                splash.classList.add('brand-splash--leaving');
                setTimeout(() => splash.remove(), 450);
            }
        }, 1800);
    })();
</script>
</body>
</html>
