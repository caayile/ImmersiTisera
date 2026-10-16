<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Loading Screen - Magang Dosen TSU</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100">
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Loading Screen Component -->
    <x-loading-screen 
        message="Selamat Datang"
        subtitle="Memuat halaman Magang Dosen TSU. Mohon tunggu sebentar..."
    />

    <!-- Main Content (shown after loading) -->
    <main class="container mx-auto px-4 py-16">
        <div class="mx-auto max-w-4xl rounded-2xl bg-white p-8 shadow-xl">
            <div class="text-center">
                <div class="mb-6 flex justify-center">
                    <img src="{{ asset('images/logo-full.png') }}" alt="Logo Magang Dosen TSU" class="h-20">
                </div>
                
                <h1 class="mb-4 text-4xl font-bold text-teal-900">Selamat Datang</h1>
                
                <p class="mb-8 text-lg text-slate-600">
                    Ini halaman utama contoh. Ganti isi <code class="rounded bg-slate-100 px-2 py-1 text-sm text-teal-700">&lt;main&gt;</code> dengan halaman Magang Dosen TSU Anda.
                </p>

                <div class="space-y-4">
                    <div class="rounded-lg border-2 border-teal-200 bg-teal-50 p-6">
                        <h3 class="mb-2 text-xl font-semibold text-teal-900">✨ Loading Screen</h3>
                        <p class="text-slate-700">
                            Loading screen dengan animasi persentase yang smooth dan modern telah berhasil diterapkan.
                        </p>
                    </div>

                    <div class="rounded-lg border-2 border-emerald-200 bg-emerald-50 p-6">
                        <h3 class="mb-2 text-xl font-semibold text-emerald-900">🎓 Sistem Sertifikat</h3>
                        <p class="text-slate-700">
                            Sistem sertifikat otomatis dengan nomor unik format <strong>001/MD.TSU/B1/X/2026</strong> sudah terintegrasi.
                        </p>
                    </div>

                    <button 
                        onclick="location.reload()"
                        class="mt-6 rounded-lg bg-teal-600 px-8 py-3 text-lg font-semibold text-white shadow-lg transition hover:bg-teal-700 hover:shadow-xl"
                    >
                        🔄 Putar ulang loading
                    </button>
                </div>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            <div class="rounded-xl bg-white p-6 shadow-lg">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-teal-100">
                    <svg class="h-6 w-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h4 class="mb-2 text-lg font-semibold text-slate-900">Nomor Sertifikat Unik</h4>
                <p class="text-sm text-slate-600">Setiap sertifikat memiliki nomor urut global yang unik dengan format terstruktur.</p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-lg">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                </div>
                <h4 class="mb-2 text-lg font-semibold text-slate-900">Tanda Tangan Digital</h4>
                <p class="text-sm text-slate-600">Mentor dapat menggambar atau upload tanda tangan untuk sertifikat peserta.</p>
            </div>

            <div class="rounded-xl bg-white p-6 shadow-lg">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-cyan-100">
                    <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                    </svg>
                </div>
                <h4 class="mb-2 text-lg font-semibold text-slate-900">Desain Modern</h4>
                <p class="text-sm text-slate-600">Template sertifikat dengan desain profesional menggunakan font Pinyon Script.</p>
            </div>
        </div>
    </main>
</body>
</html>
