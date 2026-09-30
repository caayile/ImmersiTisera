@extends('layouts.auth')
@section('title', 'Verifikasi OTP')
@php
    $mail = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>';
@endphp
@section('content')
<section class="mx-auto w-full max-w-sm rounded-[32px] bg-white px-8 py-9 text-center shadow-[0_24px_70px_rgba(30,55,80,0.16)]">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-200 bg-white shadow-sm text-zinc-700">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
    </div>
    <h1 class="mt-5 text-xl font-semibold tracking-tight text-zinc-900">Masukkan Kode OTP</h1>
    <p class="mt-2 text-sm leading-relaxed text-zinc-400">Kami telah mengirim kode 6 digit ke email Anda. Masukkan kode di bawah ini untuk verifikasi.</p>
    @if($errors->any())
        <p class="mt-4 rounded-2xl bg-red-50 px-3 py-2 text-left text-sm text-red-700">{{ $errors->first() }}</p>
    @endif
    @if(session('status'))
        <p class="mt-4 rounded-2xl bg-green-50 px-3 py-2 text-left text-sm text-green-700">{{ session('status') }}</p>
    @endif
    <form method="POST" action="{{ route('password.verify-otp.submit') }}" class="mt-6 space-y-3 text-left">
        @csrf
        <x-auth.input name="email" type="email" :value="old('email', request('email'))" placeholder="Email" :icon="$mail" required />
        <x-auth.input name="otp" type="text" :value="old('otp')" placeholder="6 digit kode OTP" maxlength="6" required />
        <button class="w-full rounded-2xl bg-zinc-900 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800">Verifikasi</button>
    </form>
    <p class="mt-5 text-sm text-zinc-400"><a href="{{ route('password.request') }}" class="font-medium text-zinc-800">Kirim ulang kode</a></p>
    <p class="mt-2 text-sm text-zinc-400"><a href="{{ route('login') }}" class="font-medium text-zinc-800">Kembali ke masuk</a></p>
</section>
@endsection
