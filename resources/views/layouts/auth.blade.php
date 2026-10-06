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

    <header class="w-full shrink-0 border-b border-gray-200 bg-white z-10 shadow-xs">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-1 sm:px-6 sm:py-1.5">
            <a href="{{ url('/') }}" class="group flex items-center space-x-2">
                <div class="flex h-6 w-6 items-center justify-center overflow-hidden rounded-none border border-gray-200 bg-white shadow-2xs sm:h-6.5 sm:w-6.5">
                    <img src="{{ asset('images/logo-tsu.svg') }}" alt="TS Group" class="h-full w-full rounded-none object-contain">
                </div>
                <div>
                    <span class="block text-xs font-extrabold leading-none tracking-tight text-forest sm:text-sm">TS Group</span>
                    <span class="mt-0.5 block text-[7.5px] font-semibold uppercase tracking-wider text-forest/70 sm:text-[8px]">Portal Akademik</span>
                </div>
            </a>

            <div class="flex items-center space-x-1.5 rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[10px] font-medium text-gray-700 shadow-2xs sm:text-[11px]">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
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
