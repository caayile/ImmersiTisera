<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') Imersi</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-bg text-ink" data-no-reveal x-data="{ open: false }">
<style>[x-cloak] { display: none !important; }</style>
@php
    $authUser = auth()->user();
    $authUser->loadMissing('participant');
    $role = $authUser->isAdmin() ? 'admin' : ($authUser->isMentor() ? 'mentor' : 'participant');
    $unread = $authUser->unreadNotifications()->count();
    $menus = [
        'participant' => [
            ['Ringkasan', 'participant.dashboard'],
            ['Profil', 'participant.profile'],
            ['Pendaftaran', 'participant.applications'],
            ['Program', 'participant.program'],
            ['Perjanjian', 'participant.agreement'],
            ['Linimasa', 'participant.timeline'],
            ['Buku Catatan', 'participant.logbooks'],
            ['Pendampingan', 'participant.mentoring'],
            ['Hasil', 'participant.outputs'],
            ['Laporan Akhir', 'participant.final-report'],
            ['Evaluasi', 'participant.evaluation'],
            ['Kolaborasi', 'participant.collaboration'],
            ['Notifikasi', 'participant.notifications'],
            ['Pengaturan', 'participant.settings'],
        ],
        'mentor' => [
            ['Ringkasan', 'mentor.dashboard'],
            ['Peserta', 'mentor.participants'],
            ['Pendaftaran', 'mentor.applications'],
            ['Program Aktif', 'mentor.programs'],
            ['Perjanjian Imersi', 'mentor.agreements'],
            ['Linimasa & Pemeriksaan', 'mentor.timeline'],
            ['Buku Catatan', 'mentor.logbooks'],
            ['Pendampingan', 'mentor.mentoring'],
            ['Hasil & Bukti', 'mentor.outputs'],
            ['Evaluasi', 'mentor.evaluations'],
            ['Alur Kolaborasi', 'mentor.collaborations'],
            ['Notifikasi', 'mentor.notifications'],
        ],
        'admin' => [
            ['Beranda', 'home'],
            ['Ringkasan', 'admin.dashboard'],
            ['Manajemen Pengguna', 'admin.users'],
            ['Unit Bisnis', 'admin.departments'],
            ['Departemen', 'admin.units'],
            ['Lowongan', 'admin.lowongan'],
            ['Mentor', 'admin.mentors'],
            ['Peserta', 'admin.participants'],
            ['Program', 'admin.programs'],
            ['Pencocokan', 'admin.matching'],
            ['Perjanjian', 'admin.agreements'],
            ['Pemantauan', 'admin.monitoring'],
            ['Evaluasi', 'admin.evaluations'],
            ['Alur Kolaborasi', 'admin.collaborations'],
            ['Laporan', 'admin.reports'],
            ['Berita', 'admin.news'],
            ['Hero Unit Bisnis', 'admin.department-hero'],
            ['Pengaturan Sistem', 'admin.settings'],
        ],
    ][$role];
@endphp

<div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
    <div x-show="open" x-cloak class="fixed inset-0 z-30 bg-black/30 lg:hidden" @click="open = false"></div>
    <aside class="fixed inset-y-0 left-0 z-40 flex w-[280px] -translate-x-full flex-col border-r border-line bg-[#f8faf9] p-5 transition lg:static lg:translate-x-0" :class="open && 'translate-x-0'">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 font-semibold">
            <img src="{{ asset('images/logo-tsu.svg') }}" alt="TSU" class="site-logo site-logo--sidebar">
            <span>Imersi
            <span class="block text-[10px] uppercase tracking-widest text-muted">TSU Industry Immersion</span>
            </span>
        </a>

        @if($role === 'participant')
            <x-sidebar-profile-card class="mt-5 shrink-0" />
        @endif

        <nav class="mt-5 min-h-0 flex-1 space-y-1 overflow-y-auto text-sm">
            @foreach($menus as [$label, $name])
                <a href="{{ route($name) }}" class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs($name) ? 'bg-white font-medium text-ink shadow-sm ring-1 ring-line' : 'text-muted hover:bg-white/70 hover:text-ink' }}">
                    <span>{{ $label }}</span>
                    @if($label === 'Notifikasi' && $unread)
                        <span class="rounded-full bg-primary px-1.5 text-[10px] font-semibold text-white">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="mt-4 shrink-0 border-t border-line pt-4">
            @csrf
            <button class="text-sm text-muted hover:text-ink">Keluar</button>
        </form>
    </aside>

    <div class="min-w-0">
        <header class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-line bg-white/95 px-5 py-3 backdrop-blur-md lg:px-8">
            <div class="flex items-center gap-2">
                <button type="button" class="rounded-lg border border-line px-3 py-1 text-sm lg:hidden" @click="open = !open">Menu</button>
                <button type="button" onclick="history.back()" class="flex items-center gap-1 rounded-lg border border-line px-3 py-1.5 text-sm font-medium text-muted hover:bg-bg hover:text-ink transition" title="Kembali">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span class="hidden sm:inline">Kembali</span>
                </button>
            </div>

            @if($role === 'participant')
                <div class="hidden min-w-0 flex-1 max-w-xl md:block relative" 
                     x-data="{ query: '{{ request('q') }}', results: [], showDropdown: false, loading: false }" 
                     @click.away="showDropdown = false">
                    <form action="{{ route('departments.index') }}" method="GET" class="w-full">
                        <label class="relative block">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted">search</span>
                            <input
                                type="search"
                                name="q"
                                x-model="query"
                                @input.debounce.300ms="
                                    if(query.length > 0) {
                                        loading = true;
                                        showDropdown = true;
                                        fetch('{{ route('api.search') }}?q=' + encodeURIComponent(query))
                                            .then(res => res.json())
                                            .then(data => { results = data; loading = false; });
                                    } else {
                                        showDropdown = false;
                                        results = [];
                                    }
                                "
                                @focus="if(query.length > 0) showDropdown = true"
                                placeholder="Cari mitra atau unit bisnis di sini..."
                                autocomplete="off"
                                class="w-full rounded-full border border-line bg-bg py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15"
                            >
                        </label>
                    </form>
                    
                    <div x-show="showDropdown" x-cloak x-transition.opacity
                         class="absolute left-0 right-0 top-full mt-2 overflow-hidden rounded-xl border border-line bg-white shadow-lg z-50 max-h-[400px] overflow-y-auto">
                        <div x-show="loading" class="p-4 text-center text-sm text-muted">Mencari...</div>
                        <div x-show="!loading && results.length === 0" class="p-4 text-center text-sm text-muted">Tidak ada hasil ditemukan.</div>
                        <ul x-show="!loading && results.length > 0" class="divide-y divide-line">
                            <template x-for="item in results" :key="item.type + item.id">
                                <li>
                                    <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-bg transition group">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition">
                                            <span class="material-symbols-outlined" x-text="item.type === 'Departemen' ? 'domain' : 'storefront'"></span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-ink" x-text="item.name"></div>
                                            <div class="text-[11px] font-medium text-muted uppercase tracking-wider mt-0.5" x-text="item.type"></div>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            @endif

            <div class="ml-auto flex items-center gap-2">
                @if(in_array($role, ['participant', 'mentor'], true))
                    <x-notification-bell />
                @endif
                <x-user-menu />
            </div>
        </header>

        <main class="px-5 py-6 lg:px-8">
            @if(session('status'))
                <div class="mb-4 rounded-lg bg-primary/15 px-4 py-3 text-sm text-primary-dark">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
