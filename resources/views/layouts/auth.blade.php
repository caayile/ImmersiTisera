<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Imersi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
</head>
<body class="auth-sky text-ink">
    <div class="auth-arcs pointer-events-none fixed inset-0"></div>

    <header class="relative z-10 px-6 py-5 sm:px-8">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
            <img src="{{ asset('images/logo-tsu.svg') }}" alt="TSU" class="site-logo site-logo--nav shadow-sm">
            <span>
                <span class="block text-[15px] font-semibold leading-none text-zinc-800">Imersi</span>
                <span class="mt-1 block text-[10px] font-medium uppercase tracking-[0.14em] text-zinc-500">TSU Industry Immersion</span>
            </span>
        </a>
    </header>

    <main class="relative z-10 flex min-h-[calc(100vh-84px)] items-center justify-center px-4 py-8">
        @if(session('status'))
            <p class="mx-auto mb-5 max-w-sm rounded-2xl bg-white/90 px-4 py-3 text-center text-sm text-primary-dark shadow-sm">{{ session('status') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>
