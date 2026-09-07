<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Imersi</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-bg text-ink" x-data="{ open: false }">
@php
    $role = auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isMentor() ? 'mentor' : 'participant');
    $unread = auth()->user()->unreadNotifications()->count();
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
            ['Ringkasan', 'admin.dashboard'],
            ['Manajemen Pengguna', 'admin.users'],
            ['Departemen', 'admin.departments'],
            ['Unit Bisnis', 'admin.units'],
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
            ['Hero Departemen', 'admin.department-hero'],
            ['Pengaturan Sistem', 'admin.settings'],
        ],
    ][$role];
@endphp

@if($role === 'participant')
<header class="sticky top-0 z-30 border-b border-line bg-white/95 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-5 py-3">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 font-semibold">
            <img src="{{ asset('images/logo-tsu.svg') }}" alt="TSU" class="site-logo site-logo--nav">
            <span class="hidden sm:block">
                Imersi
                <span class="block text-[10px] uppercase tracking-widest text-muted">TSU Industry Immersion</span>
            </span>
        </a>
        <form action="{{ route('departments.index') }}" method="GET" class="hidden min-w-0 flex-1 max-w-xl md:block">
            <label class="relative block">
                <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted">search</span>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari mitra atau unit bisnis di sini..."
                    class="w-full rounded-full border border-line bg-bg py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15"
                >
            </label>
        </form>
        <div class="flex items-center gap-2 text-sm">
            <span class="hidden rounded-full bg-bg px-3 py-1.5 text-muted lg:inline">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-full px-3 py-1.5 text-muted transition hover:bg-bg hover:text-ink">Keluar</button>
            </form>
            <button class="rounded-xl border border-line px-3 py-1.5 lg:hidden" @click="open = !open">Menu</button>
        </div>
    </div>
    <nav class="hidden border-t border-line bg-white/90 lg:block">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-1 px-5 py-2 text-sm">
            @foreach($menus as [$label, $name])
                <a href="{{ route($name) }}" class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 transition {{ request()->routeIs($name) ? 'bg-primary text-white shadow-sm' : 'text-muted hover:bg-bg hover:text-ink' }}">
                    @if($label === 'Ringkasan')
                        <span class="material-symbols-outlined text-[16px]">home</span>
                    @endif
                    {{ $label }}
                    @if($label === 'Notifikasi' && $unread)
                        <span class="ml-1 rounded-full bg-white px-1.5 text-[10px] font-semibold text-primary-dark">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>
    <div x-show="open" x-cloak class="border-t border-line bg-white px-5 py-3 text-sm lg:hidden">
        @foreach($menus as [$label, $name])
            <a href="{{ route($name) }}" class="flex items-center justify-between py-2 {{ request()->routeIs($name) ? 'font-semibold text-primary-dark' : 'text-muted' }}">
                <span>{{ $label }}</span>
                @if($label === 'Notifikasi' && $unread)
                    <span class="rounded-full bg-primary px-1.5 text-[10px] font-semibold text-white">{{ $unread }}</span>
                @endif
            </a>
        @endforeach
    </div>
</header>
<main class="mx-auto max-w-7xl px-5 py-6">
    @if(session('status'))
        <div class="mb-4 rounded-lg bg-primary/15 px-4 py-3 text-sm text-primary-dark">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
@else
<div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
    <div x-show="open" x-cloak class="fixed inset-0 z-30 bg-black/30 lg:hidden" @click="open = false"></div>
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full border-r border-line bg-white p-5 transition lg:static lg:translate-x-0" :class="open && 'translate-x-0'">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold">
            <img src="{{ asset('images/logo-tsu.svg') }}" alt="TSU" class="site-logo site-logo--sidebar">
            <span>Imersi
            <span class="block text-[10px] uppercase tracking-widest text-muted">TSU Industry Immersion</span>
            </span>
        </a>
        <nav class="mt-6 space-y-1 text-sm">
            @foreach($menus as [$label, $name])
                <a href="{{ route($name) }}" class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs($name) ? 'bg-primary text-white' : 'text-muted hover:bg-secondary/30 hover:text-ink' }}">
                    <span>{{ $label }}</span>
                    @if($label === 'Notifikasi' && $unread)
                        <span class="rounded-full bg-white px-1.5 text-[10px] font-semibold text-primary-dark">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button class="text-sm text-muted hover:text-ink">Keluar</button>
        </form>
    </aside>
    <div>
        <header class="flex items-center justify-between border-b border-line bg-white px-5 py-3 lg:px-8">
            <button class="rounded-lg border border-line px-3 py-1 text-sm lg:hidden" @click="open = !open">Menu</button>
            <p class="text-sm font-medium">{{ auth()->user()->name }} · {{ ucfirst($role) }}</p>
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
@endif
</body>
</html>
