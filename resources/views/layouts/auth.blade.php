<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — TS Group Portal Akademik</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: {
                            DEFAULT: '#0D221D',
                            dark: '#081713',
                            light: '#14332C',
                            card: '#102B24'
                        },
                        mint: {
                            DEFAULT: '#73D9B0',
                            hover: '#8CE3C2',
                            light: '#E6F9F2',
                            dark: '#41B588'
                        },
                        charcoal: '#1A1A1A'
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
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

        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #081713;
        }

        ::-webkit-scrollbar-thumb {
            background: #14332C;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #73D9B0;
        }
    </style>
</head>
<body class="bg-grid-pattern font-sans text-white min-h-screen flex flex-col justify-between selection:bg-mint selection:text-forest">

    <!-- Header / Navbar -->
    <header class="w-full bg-white border-b border-gray-200 z-10 shrink-0 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-1 sm:py-1.5 flex justify-between items-center">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 group">
                <div class="w-6 h-6 sm:w-6.5 sm:h-6.5 overflow-hidden flex items-center justify-center bg-white rounded-none border border-gray-200 shadow-2xs">
                    <img src="{{ asset('images/logo-tsu.svg') }}" alt="TS Group" class="w-full h-full object-contain rounded-none">
                </div>
                <div>
                    <span class="font-extrabold text-xs sm:text-sm tracking-tight text-forest block leading-none">TS Group</span>
                    <span class="text-[7.5px] sm:text-[8px] text-forest/70 font-semibold uppercase tracking-wider block mt-0.5">Portal Akademik</span>
                </div>
            </a>

            <div class="flex items-center space-x-1.5 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-medium text-gray-700 shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Gelombang 2026 · Sekarang Dibuka</span>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-6 flex-1 flex items-center justify-center min-h-0">
        <div class="w-full flex items-center justify-center">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-2xl text-charcoal border border-gray-100 w-full max-w-md">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer Simple -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-2 sm:py-2.5 flex justify-center items-center text-[11px] sm:text-xs text-gray-400 shrink-0 text-center">
        <div>
            &copy; 2026 Program Kolaborasi TS Group & TSU. Hak cipta dilindungi.
        </div>
    </footer>

</body>
</html>
