@extends('layouts.auth')
@section('title', 'Aktifkan Verifikasi Dua Langkah')

@section('content')
<div>
    <div class="w-12 h-12 rounded-2xl bg-forest/10 border border-forest/20 text-forest flex items-center justify-center mx-auto mb-4 shadow-sm">
        <i class="fa-solid fa-mobile-screen-button text-xl"></i>
    </div>

    <div class="text-center mb-5">
        <h2 class="text-xl font-extrabold text-charcoal tracking-tight">Amankan Akun Administrator</h2>
        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
            Tambahkan lapisan keamanan dengan aplikasi autentikator (Google Authenticator, Authy, atau yang kompatibel dengan TOTP).
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 py-2.5 px-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation shrink-0 text-sm"></i>
            <span class="break-words font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    @if (session('status'))
        <div class="mb-4 py-2.5 px-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800 flex items-center space-x-2">
            <i class="fa-solid fa-circle-check shrink-0 text-sm"></i>
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif

    <div class="mb-4 rounded-xl border border-gray-200 bg-gray-50 p-3">
        <p class="text-[11px] font-semibold text-gray-500 mb-1">Kunci rahasia (masukkan ke aplikasi autentikator):</p>
        <code class="block break-all text-xs font-bold text-charcoal select-all">{{ $secret }}</code>
        <p class="text-[10px] text-gray-400 mt-2 break-all">Tautan pendaftaran: {{ $provisioningUri }}</p>
    </div>

    @if (session('recovery_codes'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3">
            <p class="text-[11px] font-semibold text-emerald-800 mb-2">Simpan kode pemulihan ini (hanya ditampilkan sekali):</p>
            <div class="grid grid-cols-2 gap-1.5">
                @foreach (session('recovery_codes') as $recoveryCode)
                    <code class="text-xs font-bold text-emerald-900 select-all">{{ $recoveryCode }}</code>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-3.5">
        @csrf
        <div>
            <label for="two-factor-code" class="block text-xs font-semibold text-gray-700 mb-1.5">Kode 6 digit dari aplikasi</label>
            <input id="two-factor-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-bold tracking-[0.4em] text-charcoal outline-none transition focus:border-forest"
                placeholder="000000" required autofocus>
        </div>
        <button type="submit"
            class="w-full rounded-xl bg-forest py-2.5 text-sm font-bold text-white transition hover:bg-forest/90">
            Aktifkan Verifikasi Dua Langkah
        </button>
    </form>

    <p class="mt-4 text-center text-[11px] text-gray-400">
        Verifikasi dua langkah wajib untuk semua akun administrator.
    </p>
</div>
@endsection
