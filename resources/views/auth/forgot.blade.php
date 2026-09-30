@extends('layouts.auth')
@section('title', 'Lupa Kata Sandi')

@section('content')
<div>
    <!-- Icon Header -->
    <div class="w-12 h-12 rounded-2xl bg-forest/10 border border-forest/20 text-forest flex items-center justify-center mx-auto mb-4 shadow-sm">
        <i class="fa-solid fa-key text-xl"></i>
    </div>

    <!-- Title & Description -->
    <div class="text-center mb-5">
        <h2 class="text-xl font-extrabold text-charcoal tracking-tight">Lupa Kata Sandi?</h2>
        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
            Masukkan email terdaftar Anda. Kami akan mengirimkan kode OTP 6 digit untuk proses pemulihan kata sandi.
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
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-charcoal mb-1">Email Institusi / Akun</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                    <i class="fa-regular fa-envelope text-xs"></i>
                </span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    placeholder="nama@email.com"
                    class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
            </div>
        </div>

        <button type="submit"
            class="w-full py-3 px-4 bg-forest hover:bg-forest-light text-white font-bold rounded-xl text-xs sm:text-sm transition duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-forest/20 group">
            <span>Kirim Kode OTP</span>
            <i class="fa-solid fa-paper-plane text-xs group-hover:translate-x-1 transition-transform"></i>
        </button>
    </form>

    <!-- Footer link -->
    <div class="mt-5 pt-3 border-t border-gray-100 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-forest hover:text-mint-dark transition">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Kembali ke Halaman Masuk</span>
        </a>
    </div>
</div>
@endsection

