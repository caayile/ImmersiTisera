<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Magang Dosen') Program TSU</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&family=Poppins:wght@400;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500;1,9..144,600&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&family=Poppins:wght@400;600;700&display=swap"></noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (() => {
            const isPageTransition = sessionStorage.getItem('is-page-transition');
            if (isPageTransition) {
                document.documentElement.classList.add('skip-splash');
            }
        })();
    </script>
</head>
<body class="bg-white text-ink" x-data="{ open: false }">
<div id="loader" class="brand-splash" role="status" aria-label="Memuat Magang Dosen TSU">
    <div class="logo">
        <div class="part emblem"><i class="pic"></i></div>
        <div class="part l1"><i class="pic"></i></div>
        <div class="part l2"><i class="pic"></i></div>
        <div class="part tag"><i class="pic"></i></div>
        <div class="shine"></div>
    </div>
    <div class="bar">
        <div class="track"><div class="fill" id="fill"></div></div>
        <div class="pct" id="pct">0%</div>
    </div>
</div>
@php
    $nav = [
        ['Beranda', 'home'],
        ['Unit Bisnis', 'departments.index'],
        ['Berita', 'news.index'],
    ];
@endphp
<header class="sticky top-0 z-50 border-b border-line bg-white">
    <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-5 py-5 lg:px-12">
        <a href="{{ route('home') }}" class="flex items-center">
            <img id="nav-logo" src="{{ asset('images/logo-full.png') }}" alt="Magang Dosen TSU" class="h-10 sm:h-11 w-auto object-contain">
            <span id="nav-brand-text" class="sr-only">Magang Dosen TSU</span>
        </a>
        <nav class="hidden items-center gap-1 text-sm font-medium md:flex">
            @foreach($nav as [$label, $name])
                <a href="{{ route($name) }}" class="tap-feedback rounded-full px-4 py-2 {{ request()->routeIs($name) ? 'bg-primary/15 text-primary-dark' : 'text-ink hover:bg-bg' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-2 text-sm">
            @auth
                @if(auth()->user()->isMentor() || auth()->user()->isParticipant())
                    <x-notification-bell />
                @endif
                <x-user-menu />
            @else
                <a href="{{ route('login') }}" class="rounded-full bg-primary px-4 py-2 font-semibold text-white">Masuk</a>
            @endauth
            <button class="rounded-lg border border-line px-3 py-1 md:hidden" @click="open = !open">Menu</button>
        </div>
    </div>
    <div x-show="open" x-cloak class="border-t border-line bg-white px-5 py-3 text-sm md:hidden">
        @foreach($nav as [$label, $name])
            <a href="{{ route($name) }}" class="tap-feedback block py-2">{{ $label }}</a>
        @endforeach
        @auth
            <p class="border-t border-line pt-2 text-xs text-muted">{{ auth()->user()->name }}</p>
            <p class="pb-1 text-xs text-muted">{{ auth()->user()->email }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="block py-2">Keluar</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="block py-2">Masuk</a>
        @endauth
    </div>
</header>
@if(session('status'))
    <div class="bg-primary/15 px-5 py-3 text-center text-sm text-primary-dark">{{ session('status') }}</div>
@endif
<main>@yield('content')</main>
<footer class="bg-[#173d32] text-white/75">
    <div class="mx-auto grid max-w-6xl gap-10 px-5 py-14 md:grid-cols-[1.35fr_0.8fr_1fr] md:gap-16 md:py-16">
        <div>
            <div class="flex items-center">
                <img src="{{ asset('images/logo-footer.png') }}" alt="Magang Dosen TSU" class="h-16 w-auto object-contain">
            </div>
            <p class="mt-5 max-w-sm text-sm leading-6">Program ini dikelola langsung oleh unit pembelajaran dan inovasi untuk mempertemukan dosen dengan pengalaman kerja nyata di dunia industri.</p>
            <p class="mt-5 flex max-w-sm items-start gap-2 text-sm leading-6"><span class="material-symbols-outlined mt-0.5 text-[18px] text-secondary">location_on</span><span>Jl. Prof. DR. Supomo No.23, Sriwedari, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57141</span></p>
        </div>
        <div>
            <h2 class="font-semibold text-white">Navigasi</h2>
            <nav class="mt-5 flex flex-col gap-3 text-sm">
                <a href="{{ route('home') }}" class="transition hover:text-secondary">Beranda</a>
                <a href="{{ route('departments.index') }}" class="transition hover:text-secondary">Unit Bisnis</a>
                <a href="{{ route('news.index') }}" class="transition hover:text-secondary">Berita</a>
                <a href="{{ route('program.info') }}" class="transition hover:text-secondary">Pusat Informasi</a>
            </nav>
        </div>
        <div>
            <h2 class="font-semibold text-white">Kontak</h2>
            <a href="mailto:info@tiga-serangkai.com" class="mt-5 flex items-center gap-2 text-sm transition hover:text-secondary"><span class="material-symbols-outlined text-[18px] text-secondary">mail</span>info@tiga-serangkai.com</a>
            <div class="mt-6 flex items-center gap-4 text-white">
                <a href="#" aria-label="LinkedIn" class="transition hover:text-secondary"><span class="material-symbols-outlined">business</span></a>
                <a href="#" aria-label="Instagram" class="transition hover:text-secondary"><span class="material-symbols-outlined">photo_camera</span></a>
                <a href="#" aria-label="Website" class="transition hover:text-secondary"><span class="material-symbols-outlined">language</span></a>
                <a href="#" aria-label="TikTok" class="transition hover:text-secondary"><span class="material-symbols-outlined">music_note</span></a>
            </div>
        </div>
    </div>
    <div class="border-t border-white/15 px-5 py-5 text-center text-xs text-white/65">© {{ now()->year }} PT Tiga Serangkai. All rights reserved.</div>
</footer>
<script>
    (() => {
        const loader = document.getElementById('loader');
        if (!loader) return;

        const fill = document.getElementById('fill');
        const pct = document.getElementById('pct');
        const MIN = 3600; // durasi minimum loading (ms)

        const finish = () => {
            window.setTimeout(() => {
                loader.classList.add('exit');
                window.setTimeout(() => {
                    document.body.classList.add('ready');
                    sessionStorage.setItem('splash-seen', '1');
                    sessionStorage.removeItem('is-page-transition');
                    window.setTimeout(() => {
                        if (loader && loader.parentNode) loader.remove();
                    }, 1000);
                }, 350);
            }, 300);
        };

        if (document.documentElement.classList.contains('skip-splash')) {
            loader.remove();
            document.body.classList.add('ready');
            sessionStorage.removeItem('is-page-transition');
            return;
        }

        const t0 = performance.now();
        let pageLoaded = document.readyState === 'complete';
        window.addEventListener('load', () => { pageLoaded = true; });

        const tick = (now) => {
            const p = Math.min((now - t0) / MIN, 1);
            const e = 1 - Math.pow(1 - p, 3); // ease-out
            const v = Math.round((pageLoaded ? e : Math.min(e, 0.9)) * 100);
            if (fill) fill.style.width = v + '%';
            if (pct) pct.textContent = v + '%';
            if (p < 1 || !pageLoaded) {
                requestAnimationFrame(tick);
            } else {
                finish();
            }
        };

        requestAnimationFrame(tick);
    })();
</script>
</body>
</html>
