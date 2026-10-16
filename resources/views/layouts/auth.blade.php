<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — TS Group Portal Akademik</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-grid-pattern {
            background-color: #0D221D;
            background-image:
                linear-gradient(to right, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .glass-badge {
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        body.auth-shell {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
</head>
<body class="auth-shell bg-grid-pattern min-h-screen flex flex-col justify-between text-white selection:bg-mint selection:text-forest">

    <header class="sticky top-0 z-50 border-b border-line bg-white shrink-0">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-5 py-5 lg:px-12">
            <a href="{{ url('/') }}" class="group flex items-center">
                <img id="nav-logo" src="{{ asset('images/logo-full.png') }}" alt="Magang Dosen TSU" class="h-10 sm:h-11 w-auto object-contain">
                <span id="nav-brand-text" class="sr-only">Magang Dosen TSU</span>
            </a>

            <div class="flex items-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3.5 py-1.5 text-xs font-semibold text-gray-700 shadow-2xs">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Gelombang 2026 · Sekarang Dibuka</span>
            </div>
        </div>
    </header>

    <main class="mx-auto flex w-full max-w-7xl flex-1 items-center justify-center px-4 py-6 sm:px-6 min-h-0">
        <div class="flex w-full items-center justify-center">
            <div class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-5 text-charcoal shadow-2xl sm:rounded-3xl sm:p-7">
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="mx-auto flex w-full max-w-7xl shrink-0 items-center justify-center px-4 py-2 text-center text-[11px] text-gray-400 sm:px-6 sm:py-2.5 sm:text-xs">
        <div>&copy; 2026 Program Kolaborasi TS Group & TSU. Hak cipta dilindungi.</div>
    </footer>

</body>
</html>
