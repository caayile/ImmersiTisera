@extends('layouts.auth')
@section('title', 'Verifikasi Dua Langkah')

@section('content')
<div>
    <div class="w-12 h-12 rounded-2xl bg-forest/10 border border-forest/20 text-forest flex items-center justify-center mx-auto mb-4 shadow-sm">
        <i class="fa-solid fa-shield-halved text-xl"></i>
    </div>

    <div class="text-center mb-5">
        <h2 class="text-xl font-extrabold text-charcoal tracking-tight">Verifikasi Dua Langkah</h2>
        <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
            Masukkan kode 6 digit dari aplikasi autentikator Anda, atau salah satu kode pemulihan.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-4 py-2.5 px-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-center space-x-2">
            <i class="fa-solid fa-circle-exclamation shrink-0 text-sm"></i>
            <span class="break-words font-medium">{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.challenge.submit') }}" class="space-y-3.5">
        @csrf
        <div>
            <label for="two-factor-challenge-code" class="block text-xs font-semibold text-gray-700 mb-1.5">Kode autentikator</label>
            <input id="two-factor-challenge-code" name="code" type="text" inputmode="text" autocomplete="one-time-code" maxlength="32"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-bold tracking-[0.2em] text-charcoal outline-none transition focus:border-forest"
                placeholder="000000" required autofocus>
        </div>
        <button type="submit"
            class="w-full rounded-xl bg-forest py-2.5 text-sm font-bold text-white transition hover:bg-forest/90">
            Verifikasi dan Masuk
        </button>
    </form>
</div>
@endsection
