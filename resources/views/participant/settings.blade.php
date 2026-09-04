@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<h1 class="text-2xl font-semibold">Pengaturan</h1>
<form method="POST" class="mt-6 max-w-md space-y-4 rounded-2xl border border-line bg-white p-6">
    @csrf
    <input type="password" name="password" placeholder="Kata sandi baru" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    <input type="password" name="password_confirmation" placeholder="Konfirmasi" class="w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Perbarui kata sandi</button>
</form>
@endsection
