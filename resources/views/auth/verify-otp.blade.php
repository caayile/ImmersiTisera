@extends('layouts.auth')
@section('title', 'Verifikasi Kode OTP')

@section('content')
@php
    $targetEmail = old('email', request('email', session('email', '')));
@endphp
<div>
    <!-- Icon Header -->
    <div class="w-12 h-12 rounded-2xl bg-mint/20 border border-mint/40 text-forest flex items-center justify-center mx-auto mb-4 shadow-sm">
        <i class="fa-solid fa-shield-halved text-xl"></i>
    </div>

    <!-- Title & Description -->
    <div class="text-center mb-5">
        <h2 class="text-xl font-extrabold text-charcoal tracking-tight">Masukkan Kode OTP</h2>
        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
            Kode 6 digit telah dikirim ke email:<br>
            <span class="font-bold text-charcoal break-all">{{ $targetEmail ?: 'alamat email Anda' }}</span>
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
    <form method="POST" action="{{ route('password.verify-otp.submit') }}" class="space-y-4">
        @csrf
        <!-- Email Input (read-only or hidden if email present, or input if missing) -->
        @if ($targetEmail)
            <input type="hidden" name="email" value="{{ $targetEmail }}">
        @else
            <div>
                <label class="block text-xs font-bold text-charcoal mb-1">Email Institusi / Akun</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                        <i class="fa-regular fa-envelope text-xs"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="nama@email.com"
                        class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-charcoal placeholder-gray-400 focus:bg-white focus:outline-none focus:border-forest focus:ring-2 focus:ring-forest/10 transition">
                </div>
            </div>
        @endif

        <div>
            <label class="block text-xs font-bold text-charcoal mb-2 text-center">Masukkan 6 Digit Kode OTP</label>
            <div class="flex justify-center gap-2" id="otp-inputs">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" name="otp_digits[]"
                        class="otp-digit w-10 h-12 sm:w-11 sm:h-12 text-center text-lg sm:text-xl font-extrabold border border-gray-300 rounded-xl focus:border-forest focus:ring-2 focus:ring-forest/20 focus:outline-none transition bg-gray-50 focus:bg-white text-forest selection:bg-mint"
                        maxlength="1" inputmode="numeric" pattern="[0-9]*" required
                        autocomplete="off" aria-label="Digit OTP {{ $i + 1 }}">
                @endfor
            </div>
        </div>

        <button type="submit"
            class="w-full py-3 px-4 bg-forest hover:bg-forest-light text-white font-bold rounded-xl text-xs sm:text-sm transition duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-forest/20 group">
            <span>Verifikasi Kode OTP</span>
            <i class="fa-solid fa-circle-check text-xs group-hover:scale-110 transition-transform"></i>
        </button>
    </form>

    <!-- Resend Form -->
    @if ($targetEmail)
        <form method="POST" action="{{ route('password.email') }}" class="mt-3 text-center">
            @csrf
            <input type="hidden" name="email" value="{{ $targetEmail }}">
            <button type="submit" class="text-xs text-gray-500 hover:text-forest font-semibold transition inline-flex items-center space-x-1.5 py-1 px-2 rounded-lg hover:bg-gray-100">
                <i class="fa-solid fa-rotate-right text-[10px]"></i>
                <span>Belum menerima kode? Kirim ulang OTP</span>
            </button>
        </form>
    @endif

    <!-- Footer Links -->
    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
        <a href="{{ route('password.request') }}" class="font-semibold text-gray-500 hover:text-charcoal transition flex items-center space-x-1">
            <i class="fa-regular fa-pen-to-square text-[10px]"></i>
            <span>Ganti Email</span>
        </a>
        <a href="{{ route('login') }}" class="font-bold text-forest hover:text-mint-dark transition flex items-center space-x-1">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Kembali ke Masuk</span>
        </a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const digits = document.querySelectorAll('.otp-digit');
        if (digits.length === 0) return;

        // Auto focus first empty input
        let focused = false;
        digits.forEach(input => {
            if (!input.value && !focused) {
                input.focus();
                focused = true;
            }
        });
        if (!focused) digits[0].focus();

        digits.forEach((input, index) => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value && index < digits.length - 1) {
                    digits[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    digits[index - 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                pasted.split('').forEach((char, i) => {
                    if (digits[i]) digits[i].value = char;
                });
                if (pasted.length > 0) {
                    const nextIndex = Math.min(pasted.length, digits.length - 1);
                    digits[nextIndex].focus();
                }
            });
        });
    });
</script>
@endsection

