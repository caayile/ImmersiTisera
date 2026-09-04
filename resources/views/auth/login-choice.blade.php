@extends('layouts.auth')
@section('title', 'Masuk')
@php
    $side = session('auth_side') === 'mentor' ? 'mentor' : 'dosen';
    $mail = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>';
@endphp
@section('content')
<section
    class="mx-auto w-full max-w-sm rounded-[32px] bg-white px-8 py-9 text-center shadow-[0_24px_70px_rgba(30,55,80,0.16)]"
    x-data="{ role: '{{ $side }}' }"
>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-zinc-200 bg-white shadow-sm text-zinc-700">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
    </div>
    <h1 class="mt-5 text-xl font-semibold tracking-tight text-zinc-900">Masuk dengan email</h1>
    <p class="mt-2 text-sm leading-relaxed text-zinc-400">Pilih peran, lalu masuk ke Imersi. Admin program masuk sebagai dosen.</p>

    <div class="mt-5 grid grid-cols-2 gap-2 rounded-2xl bg-zinc-100 p-1">
        <button type="button" class="rounded-xl py-2.5 text-sm font-semibold transition" :class="role === 'dosen' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-400'" @click="role = 'dosen'">Masuk Dosen</button>
        <button type="button" class="rounded-xl py-2.5 text-sm font-semibold transition" :class="role === 'mentor' ? 'bg-white text-zinc-900 shadow-sm' : 'text-zinc-400'" @click="role = 'mentor'">Masuk Mentor</button>
    </div>

    @if($errors->any())
        <p class="mt-4 rounded-2xl bg-red-50 px-3 py-2 text-left text-sm text-red-700">{{ $errors->first() }}</p>
    @endif

    <form method="POST" :action="role === 'mentor' ? '{{ route('login.mentor') }}' : '{{ route('login.user') }}'" class="mt-6 space-y-3 text-left">
        @csrf
        <x-auth.input name="email" type="email" :value="old('email')" placeholder="Email" :icon="$mail" required />
        <x-auth.password placeholder="Password" />
        <div class="flex justify-end pt-0.5">
            <a href="{{ route('password.request') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-800">Lupa kata sandi?</a>
        </div>
        <button class="w-full rounded-2xl bg-zinc-900 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800">Masuk</button>
    </form>

    <p class="mt-5 text-sm text-zinc-400">Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-zinc-800">Daftar</a></p>
</section>
@endsection
