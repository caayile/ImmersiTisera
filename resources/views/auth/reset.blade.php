@extends('layouts.auth')
@section('title', 'Reset kata sandi')
@php
    $mail = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>';
@endphp
@section('content')
<section class="mx-auto w-full max-w-sm rounded-[32px] bg-white px-8 py-9 text-center shadow-[0_24px_70px_rgba(30,55,80,0.16)]">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-200 bg-white shadow-sm text-zinc-700">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
    </div>
    <h1 class="mt-5 text-xl font-semibold tracking-tight text-zinc-900">Kata sandi baru</h1>
    <p class="mt-2 text-sm leading-relaxed text-zinc-400">Buat kata sandi baru untuk akun Imersi Anda.</p>
    @if($errors->any())
        <p class="mt-4 rounded-2xl bg-red-50 px-3 py-2 text-left text-sm text-red-700">{{ $errors->first() }}</p>
    @endif
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-3 text-left">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-auth.input name="email" type="email" :value="$email" placeholder="Email" :icon="$mail" required />
        <x-auth.password placeholder="Password baru" />
        <x-auth.password name="password_confirmation" placeholder="Konfirmasi password" />
        <button class="w-full rounded-2xl bg-zinc-900 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800">Simpan</button>
    </form>
</section>
@endsection
