@php
    $defaultRole = session('auth_side') === 'mentor' ? 'mentor' : 'dosen';
    $defaultTab = $initialTab ?? (request()->routeIs('register*') || request('tab') === 'register' ? 'register' : 'login');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Dosen & Mentor - TS Group</title>
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

        .tab-content {
            display: none;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }
        .tab-content.active {
            display: block;
            opacity: 1;
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
<body class="bg-grid-pattern font-sans text-white min-h-screen lg:h-screen lg:max-h-screen lg:overflow-hidden flex flex-col justify-between selection:bg-mint selection:text-forest">

    <!-- Header / Navbar -->
    <header class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-2.5 sm:py-3.5 flex justify-between items-center z-10 shrink-0">
        <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-mint flex items-center justify-center text-forest font-bold text-lg sm:text-xl shadow-lg shadow-mint/20 group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div>
                <span class="font-extrabold text-lg sm:text-xl tracking-tight text-white block leading-none">TS Group</span>
                <span class="text-[9px] sm:text-[10px] text-mint font-semibold uppercase tracking-widest">Portal Akademik</span>
            </div>
        </a>

        <div class="flex items-center space-x-2 glass-badge px-3.5 py-1.5 rounded-full text-xs font-medium text-gray-300">
            <span class="w-2 h-2 rounded-full bg-mint animate-pulse"></span>
            <span>Gelombang 2026 · Sekarang Dibuka</span>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-1 lg:py-2 flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-center min-h-0 overflow-hidden">
        
        <!-- Left Side: Branding, Tagline, & Info Cards -->
        <div class="lg:col-span-7 flex flex-col justify-center space-y-3 sm:space-y-4">
            <!-- Badge -->
            <div class="inline-flex items-center space-x-2 glass-badge px-3 py-1 rounded-full text-xs font-medium text-mint w-fit">
                <span class="w-1.5 h-1.5 rounded-full bg-mint animate-pulse"></span>
                <span>Program Imersi Industri TS Group</span>
            </div>

            <!-- Title & Subtitle -->
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-4xl lg:text-[42px] font-extrabold text-white leading-[1.15] tracking-tight">
                    Mulai kolaborasi dari <br class="hidden sm:block"/>
                    <span class="text-mint underline decoration-mint/30 decoration-wavy underline-offset-4">unit bisnis yang tepat</span><br class="hidden sm:block"/>
                    di sini.
                </h1>
                <p class="text-gray-300 text-xs sm:text-sm lg:text-[15px] max-w-xl font-normal leading-relaxed">
                    Program khusus untuk menghubungkan <strong class="text-white font-semibold">Dosen TSU</strong> dan <strong class="text-white font-semibold">Mentor Profesional</strong> dengan ekosistem Unit Bisnis TS Group.
                </p>
            </div>

            <!-- 4 Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                <!-- Card 1 -->
                <div class="bg-forest-card/80 border border-white/10 rounded-xl p-3 hover:border-mint/50 transition duration-300 backdrop-blur-sm group">
                    <div class="flex items-center space-x-2.5 mb-1.5">
                        <div class="w-7 h-7 rounded-lg bg-mint/10 text-mint flex items-center justify-center group-hover:bg-mint group-hover:text-forest transition duration-300 shrink-0">
                            <i class="fa-solid fa-chart-line text-xs"></i>
                        </div>
                        <h3 class="font-bold text-white text-xs sm:text-sm">Wawasan Industri</h3>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-snug">Memahami alur kerja dan keputusan bisnis secara langsung.</p>
                </div>

                <!-- Card 2 -->
                <div class="bg-forest-card/80 border border-white/10 rounded-xl p-3 hover:border-mint/50 transition duration-300 backdrop-blur-sm group">
                    <div class="flex items-center space-x-2.5 mb-1.5">
                        <div class="w-7 h-7 rounded-lg bg-mint/10 text-mint flex items-center justify-center group-hover:bg-mint group-hover:text-forest transition duration-300 shrink-0">
                            <i class="fa-solid fa-user-tie text-xs"></i>
                        </div>
                        <h3 class="font-bold text-white text-xs sm:text-sm">Mentor Profesional</h3>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-snug">Mentoring rutin & bimbingan praktisi ahli industri.</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-forest-card/80 border border-white/10 rounded-xl p-3 hover:border-mint/50 transition duration-300 backdrop-blur-sm group">
                    <div class="flex items-center space-x-2.5 mb-1.5">
                        <div class="w-7 h-7 rounded-lg bg-mint/10 text-mint flex items-center justify-center group-hover:bg-mint group-hover:text-forest transition duration-300 shrink-0">
                            <i class="fa-solid fa-handshake text-xs"></i>
                        </div>
                        <h3 class="font-bold text-white text-xs sm:text-sm">Kolaborasi Lanjutan</h3>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-snug">Kuliah tamu, riset bersama, hingga penyusunan kurikulum.</p>
                </div>

                <!-- Card 4 -->
                <div class="bg-forest-card/80 border border-white/10 rounded-xl p-3 hover:border-mint/50 transition duration-300 backdrop-blur-sm group">
                    <div class="flex items-center space-x-2.5 mb-1.5">
                        <div class="w-7 h-7 rounded-lg bg-mint/10 text-mint flex items-center justify-center group-hover:bg-mint group-hover:text-forest transition duration-300 shrink-0">
                            <i class="fa-solid fa-clipboard-check text-xs"></i>
                        </div>
                        <h3 class="font-bold text-white text-xs sm:text-sm">Hasil & Evaluasi Terukur</h3>
                    </div>
                    <p class="text-[11px] text-gray-400 leading-snug">Rekaman aktivitas, bukti kerja nyata, dan rekognisi dampak.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: White Login / Register Form Card -->
        <div class="lg:col-span-5 w-full flex items-center justify-center">
            <div class="bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-6 shadow-2xl text-charcoal border border-gray-100 w-full max-w-md">
                
                <!-- Main Tab Switcher (Masuk vs Daftar) -->
                <div class="flex bg-gray-100 p-1 rounded-xl mb-3.5">
                    <button id="btn-tab-login" type="button" onclick="switchMainTab('login')" class="flex-1 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all duration-200 bg-white text-charcoal shadow-sm">
                        Masuk Portal
                    </button>
                    <button id="btn-tab-register" type="button" onclick="switchMainTab('register')" class="flex-1 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all duration-200 text-gray-500 hover:text-charcoal">
                        Daftar Akun
                    </button>
                </div>

                <!-- Role Selector Toggle (Dosen vs Mentor) -->
                <div class="mb-3.5">
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="role_selector" value="dosen" id="role-dosen" {{ $defaultRole === 'dosen' ? 'checked' : '' }} class="peer sr-only" onchange="updateRoleUI('dosen')">
                            <div class="border border-gray-200 peer-checked:border-forest peer-checked:bg-forest/5 peer-checked:text-forest rounded-xl py-2 px-2.5 text-center transition flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-chalkboard-user text-sm"></i>
                                <span class="font-bold text-xs">
                                    <span class="role-label-tab-login {{ $defaultTab === 'login' ? 'inline' : 'hidden' }}">Masuk Dosen</span>
                                    <span class="role-label-tab-reg {{ $defaultTab === 'register' ? 'inline' : 'hidden' }}">Daftar Dosen</span>
                                </span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role_selector" value="mentor" id="role-mentor" {{ $defaultRole === 'mentor' ? 'checked' : '' }} class="peer sr-only" onchange="updateRoleUI('mentor')">
                            <div class="border border-gray-200 peer-checked:border-forest peer-checked:bg-forest/5 peer-checked:text-forest rounded-xl py-2 px-2.5 text-center transition flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-user-gear text-sm"></i>
                                <span class="font-bold text-xs">
                                    <span class="role-label-tab-login {{ $defaultTab === 'login' ? 'inline' : 'hidden' }}">Masuk Mentor</span>
                                    <span class="role-label-tab-reg {{ $defaultTab === 'register' ? 'inline' : 'hidden' }}">Daftar Mentor</span>
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                @if($errors->any())
                    <div class="mb-3 py-2 px-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-center space-x-2">
                        <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                        <span class="truncate">{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('status'))
                    <div class="mb-3 py-2 px-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center space-x-2">
                        <i class="fa-solid fa-circle-check shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- ================= TAB 1: FORM LOGIN ================= -->
                <div id="tab-login" class="tab-content {{ $defaultTab === 'login' ? 'active' : '' }}">
                    <div class="mb-3">
                        <h2 class="text-lg sm:text-xl font-extrabold text-charcoal tracking-tight" id="login-title">
                            {{ $defaultRole === 'mentor' ? 'Masuk Portal Mentor' : 'Masuk Portal Dosen' }}
                        </h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Masukkan kredensial akun Anda untuk mengakses dashboard.</p>
                    </div>

                    <form id="form-login" method="POST" action="{{ $defaultRole === 'mentor' ? route('login.mentor') : route('login.user') }}" class="space-y-2.5">
                        @csrf
                        <!-- Email Input -->
                        <div>
                            <label class="block text-[11px] font-bold text-charcoal mb-1">Email Resmi</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <i class="fa-regular fa-envelope text-xs"></i>
                                </span>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-[11px] font-bold text-charcoal">Kata Sandi</label>
                                <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-forest hover:text-mint-dark transition">Lupa sandi?</a>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                </span>
                                <input type="password" id="login-password" name="password" required placeholder="••••••••" class="w-full pl-8 pr-8 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                                <button type="button" onclick="togglePassword('login-password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-charcoal">
                                    <i class="fa-regular fa-eye text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="flex items-center pt-0.5">
                            <input type="checkbox" name="remember" id="remember" class="w-3.5 h-3.5 text-forest border-gray-300 rounded focus:ring-forest">
                            <label for="remember" class="ml-2 text-[11px] font-medium text-gray-600">Ingat sesi saya di perangkat ini</label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-2.5 px-4 bg-forest hover:bg-forest-light text-white font-bold rounded-xl text-xs sm:text-sm transition duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-forest/20 group mt-1">
                            <span id="btn-login-text">Masuk ke Dashboard</span>
                            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </form>
                </div>

                <!-- ================= TAB 2: FORM REGISTER ================= -->
                <div id="tab-register" class="tab-content {{ $defaultTab === 'register' ? 'active' : '' }}">
                    <div class="mb-2.5">
                        <h2 class="text-lg sm:text-xl font-extrabold text-charcoal tracking-tight" id="reg-title">
                            {{ $defaultRole === 'mentor' ? 'Pendaftaran Mentor Industri' : 'Pendaftaran Dosen TSU' }}
                        </h2>
                        <p class="text-[11px] text-gray-500 mt-0.5">Isi formulir berikut untuk membuat akun kolaborasi baru.</p>
                    </div>

                    <form id="form-register" method="POST" action="{{ $defaultRole === 'mentor' ? route('register.mentor') : route('register.user') }}" class="space-y-2">
                        @csrf
                        
                        <!-- Row 1: Name & Email -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-charcoal mb-1">Nama Lengkap & Gelar</label>
                                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Dr. Budi Santoso, M.T." class="w-full px-2.5 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-charcoal mb-1">Email Resmi</label>
                                <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@instansi.com" class="w-full px-2.5 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                            </div>
                        </div>

                        <!-- Row 2: WhatsApp & Unit Bisnis (for Mentor) / Info (for Dosen) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-charcoal mb-1">Nomor WhatsApp</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="081234567890" class="w-full px-2.5 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                            </div>
                            <div id="container-mentor-dept" class="{{ $defaultRole === 'mentor' ? 'block' : 'hidden' }}">
                                <label class="block text-[11px] font-bold text-charcoal mb-1">Unit Bisnis TS Group</label>
                                <select name="department_id" class="w-full px-2.5 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                                    <option value="">Pilih Unit Bisnis</option>
                                    @if(isset($departments))
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div id="container-dosen-instansi" class="{{ $defaultRole === 'dosen' ? 'block' : 'hidden' }}">
                                <label class="block text-[11px] font-bold text-charcoal mb-1">Perguruan Tinggi</label>
                                <input type="text" placeholder="Telkom University / Mitra" class="w-full px-2.5 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                            </div>
                        </div>

                        <!-- Row 3: Password & Confirm Password -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-charcoal mb-1">Kata Sandi</label>
                                <div class="relative">
                                    <input type="password" id="reg-password" name="password" required placeholder="Min. 6 karakter" class="w-full pl-2.5 pr-7 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                                    <button type="button" onclick="togglePassword('reg-password', this)" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-charcoal">
                                        <i class="fa-regular fa-eye text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-charcoal mb-1">Konfirmasi Sandi</label>
                                <div class="relative">
                                    <input type="password" id="reg-password-confirm" name="password_confirmation" required placeholder="Ulangi sandi" class="w-full pl-2.5 pr-7 py-1.5 sm:py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                                    <button type="button" onclick="togglePassword('reg-password-confirm', this)" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-charcoal">
                                        <i class="fa-regular fa-eye text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Agreement Checkbox -->
                        <div class="flex items-center pt-0.5">
                            <input type="checkbox" required id="terms" checked class="w-3.5 h-3.5 text-forest border-gray-300 rounded focus:ring-forest shrink-0">
                            <label for="terms" class="ml-2 text-[10px] text-gray-500 leading-tight">
                                Menyetujui ketentuan verifikasi data akademik & kemitraan industri TS Group.
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full py-2.5 px-4 bg-forest hover:bg-forest-light text-white font-bold rounded-xl text-xs sm:text-sm transition duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-forest/20 group mt-1">
                            <span id="btn-reg-text">{{ $defaultRole === 'mentor' ? 'Kirim Pendaftaran Mentor' : 'Kirim Pendaftaran Dosen' }}</span>
                            <i class="fa-solid fa-paper-plane text-xs group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </form>
                </div>

                <!-- Footer Switcher Link -->
                <div class="mt-3 pt-2.5 border-t border-gray-100 text-center">
                    <p class="text-[11px] text-gray-500" id="footer-switch-text">
                        @if($defaultTab === 'login')
                            Belum memiliki akun kolaborasi? 
                            <button type="button" onclick="switchMainTab('register')" class="font-bold text-forest hover:underline">Daftar sekarang</button>
                        @else
                            Sudah memiliki akun terdaftar? 
                            <button type="button" onclick="switchMainTab('login')" class="font-bold text-forest hover:underline">Masuk sekarang</button>
                        @endif
                    </p>
                </div>

            </div>
        </div>

    </main>

    <!-- Footer Simple -->
    <footer class="w-full max-w-7xl mx-auto px-4 sm:px-6 py-2 sm:py-3 flex flex-col sm:flex-row justify-between items-center text-[11px] sm:text-xs text-gray-400 gap-1.5 sm:gap-4 shrink-0">
        <div>
            &copy; 2026 TS Group & TSU Collaboration Program. All rights reserved.
        </div>
        <div class="flex space-x-4 sm:space-x-6">
            <a href="{{ route('program.info') }}" class="hover:text-mint transition">Panduan Program</a>
            <a href="{{ route('home') }}#kontak" class="hover:text-mint transition">Pusat Bantuan</a>
            <a href="#" class="hover:text-mint transition">Privasi & Keamanan</a>
        </div>
    </footer>

    <!-- Interactive JavaScript -->
    <script>
        let currentRole = '{{ $defaultRole }}';
        let currentTab = '{{ $defaultTab }}';

        const routes = {
            loginUser: '{{ route('login.user') }}',
            loginMentor: '{{ route('login.mentor') }}',
            registerUser: '{{ route('register.user') }}',
            registerMentor: '{{ route('register.mentor') }}',
        };

        function switchMainTab(tab) {
            currentTab = tab;
            const btnLogin = document.getElementById('btn-tab-login');
            const btnReg = document.getElementById('btn-tab-register');
            const tabLogin = document.getElementById('tab-login');
            const tabReg = document.getElementById('tab-register');
            const footerText = document.getElementById('footer-switch-text');
            const loginLabels = document.querySelectorAll('.role-label-tab-login');
            const regLabels = document.querySelectorAll('.role-label-tab-reg');

            if (tab === 'login') {
                btnLogin.className = 'flex-1 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all duration-200 bg-white text-charcoal shadow-sm';
                btnReg.className = 'flex-1 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all duration-200 text-gray-500 hover:text-charcoal';
                
                tabLogin.classList.add('active');
                tabReg.classList.remove('active');

                loginLabels.forEach(el => el.classList.remove('hidden'));
                loginLabels.forEach(el => el.classList.add('inline'));
                regLabels.forEach(el => el.classList.remove('inline'));
                regLabels.forEach(el => el.classList.add('hidden'));

                footerText.innerHTML = 'Belum memiliki akun kolaborasi? <button type="button" onclick="switchMainTab(\'register\')" class="font-bold text-forest hover:underline">Daftar sekarang</button>';
            } else {
                btnReg.className = 'flex-1 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all duration-200 bg-white text-charcoal shadow-sm';
                btnLogin.className = 'flex-1 py-2 rounded-lg font-bold text-xs sm:text-sm transition-all duration-200 text-gray-500 hover:text-charcoal';

                tabReg.classList.add('active');
                tabLogin.classList.remove('active');

                loginLabels.forEach(el => el.classList.remove('inline'));
                loginLabels.forEach(el => el.classList.add('hidden'));
                regLabels.forEach(el => el.classList.remove('hidden'));
                regLabels.forEach(el => el.classList.add('inline'));

                footerText.innerHTML = 'Sudah memiliki akun terdaftar? <button type="button" onclick="switchMainTab(\'login\')" class="font-bold text-forest hover:underline">Masuk sekarang</button>';
            }
        }

        function updateRoleUI(role) {
            currentRole = role;
            const loginTitle = document.getElementById('login-title');
            const regTitle = document.getElementById('reg-title');
            const formLogin = document.getElementById('form-login');
            const formReg = document.getElementById('form-register');
            const btnRegText = document.getElementById('btn-reg-text');
            const containerMentorDept = document.getElementById('container-mentor-dept');
            const containerDosenInstansi = document.getElementById('container-dosen-instansi');

            if (role === 'dosen') {
                loginTitle.innerText = 'Masuk Portal Dosen';
                regTitle.innerText = 'Pendaftaran Dosen TSU';
                formLogin.action = routes.loginUser;
                formReg.action = routes.registerUser;
                btnRegText.innerText = 'Kirim Pendaftaran Dosen';
                
                containerMentorDept.classList.add('hidden');
                containerMentorDept.classList.remove('block');
                containerDosenInstansi.classList.add('block');
                containerDosenInstansi.classList.remove('hidden');
            } else {
                loginTitle.innerText = 'Masuk Portal Mentor';
                regTitle.innerText = 'Pendaftaran Mentor Industri';
                formLogin.action = routes.loginMentor;
                formReg.action = routes.registerMentor;
                btnRegText.innerText = 'Kirim Pendaftaran Mentor';

                containerMentorDept.classList.add('block');
                containerMentorDept.classList.remove('hidden');
                containerDosenInstansi.classList.add('hidden');
                containerDosenInstansi.classList.remove('block');
            }
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash text-xs';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye text-xs';
            }
        }
    </script>
</body>
</html>
