@extends('layouts.auth')
@section('title', 'Reset Kata Sandi')

@section('content')
<div>
    <!-- Icon Header -->
    <div class="w-12 h-12 rounded-2xl bg-forest/10 border border-forest/20 text-forest flex items-center justify-center mx-auto mb-4 shadow-sm">
        <i class="fa-solid fa-lock text-xl"></i>
    </div>

    <!-- Title & Description -->
    <div class="text-center mb-5">
        <h2 class="text-xl font-extrabold text-charcoal tracking-tight">Buat Kata Sandi Baru</h2>
        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
            Email terverifikasi untuk <strong class="text-charcoal font-semibold">{{ $email }}</strong>.<br>
            Silakan buat kata sandi baru untuk akun Anda.
        </p>
    </div>

    <!-- Alert Errors -->
    @if ($errors->any())
        <div class="mb-4 py-2.5 px-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation shrink-0 text-sm"></i>
            <span class="break-words font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    <!-- Alert Status -->
    @if (session('status'))
        <div class="mb-4 py-2.5 px-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center space-x-2">
            <i class="fa-solid fa-circle-check shrink-0 text-sm"></i>
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('password.update') }}" class="space-y-3.5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <!-- Password Baru -->
        <div>
            <label class="block text-xs font-bold text-charcoal mb-1">Kata Sandi Baru</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                    <i class="fa-solid fa-key text-xs"></i>
                </span>
                <input type="password" id="reset-password" name="password" required autofocus
                    placeholder="Minimal 6 karakter"
                    class="w-full pl-9 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                <button type="button" onclick="togglePassword('reset-password', this)"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-charcoal">
                    <i class="fa-regular fa-eye text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Konfirmasi Password Baru -->
        <div>
            <label class="block text-xs font-bold text-charcoal mb-1">Konfirmasi Kata Sandi Baru</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                </span>
                <input type="password" id="reset-password-confirm" name="password_confirmation" required
                    placeholder="Ulangi kata sandi baru"
                    class="w-full pl-9 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                <button type="button" onclick="togglePassword('reset-password-confirm', this)"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-charcoal">
                    <i class="fa-regular fa-eye text-xs"></i>
                </button>
            </div>
        </div>

        <button type="submit"
            class="w-full py-3 px-4 bg-forest hover:bg-forest-light text-white font-bold rounded-xl text-xs sm:text-sm transition duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-forest/20 group mt-2">
            <span>Simpan Kata Sandi Baru</span>
            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
        </button>
    </form>

    <!-- Footer link -->
    <div class="mt-5 pt-3 border-t border-gray-100 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-forest hover:text-mint-dark transition">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Batal & Kembali ke Halaman Masuk</span>
        </a>
    </div>
</div>

<script>
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
@endsection

