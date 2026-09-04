<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Imersi') — TSU Industry Immersion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="bg-white text-ink" x-data="{ open: false }">
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
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-white">I</span>
            <span>Imersi
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
</body>
</html>
