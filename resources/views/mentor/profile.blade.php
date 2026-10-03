@extends('layouts.app')
@section('title', 'Profil Saya')
@section('content')
@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
@endphp

<div
    class="mx-auto max-w-5xl"
    x-data="{
        avatarPreview: @js($user->avatarUrl()),
        avatarName: '',
    }"
>
    <section class="overflow-hidden rounded-3xl border border-line bg-white shadow-sm">
        <div class="hero-grid px-6 py-8 md:px-10">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <template x-if="avatarPreview">
                    <img :src="avatarPreview" alt="Foto profil" width="80" height="80" class="h-20 w-20 shrink-0 rounded-2xl object-cover shadow-sm">
                </template>
                <template x-if="!avatarPreview">
                    <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl font-semibold text-primary-dark shadow-sm">{{ $initials }}</span>
                </template>
                <div class="text-white">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">Profil mentor · Magang Dosen</p>
                    <h1 class="mt-1 text-3xl font-semibold tracking-tight md:text-4xl">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-white/75">Lengkapi data profesional agar dosen mengenal Anda.</p>
                </div>
                <a href="{{ route('profile.public') }}" class="inline-flex items-center gap-1 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-ink sm:ml-auto">
                    Lihat profil publik
                    <span class="material-symbols-outlined text-[18px]">arrow_outward</span>
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('mentor.profile.update') }}" enctype="multipart/form-data" class="space-y-8 p-6 md:p-10">
            @csrf

            <section>
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/15 text-primary-dark">
                        <span class="material-symbols-outlined text-[22px]">badge</span>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold">Identitas</h2>
                        <p class="mt-1 text-sm text-muted">Data dasar yang tampil di dasbor dan profil publik.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Foto profil</span>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <input id="profile-avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="sr-only" @change="avatarName = $event.target.files[0]?.name ?? ''; if ($event.target.files[0]) avatarPreview = URL.createObjectURL($event.target.files[0])">
                            <label for="profile-avatar" class="inline-flex cursor-pointer items-center rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition hover:border-primary">Pilih foto</label>
                            <span class="min-w-[12rem] flex-1 rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-muted" x-text="avatarName || 'Belum ada file dipilih'">Belum ada file dipilih</span>
                        </div>
                        <p class="mt-1 text-xs text-muted">Opsional. JPG, PNG, atau WebP, maksimal 5 MB.</p>
                        @error('avatar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <label class="md:col-span-2">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Nama lengkap</span>
                        <input name="name" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15" required>
                    </label>
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Telepon</span>
                        <input name="phone" value="{{ old('phone', $user->phone) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15">
                    </label>
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Nomor Induk Karyawan (NIK)</span>
                        <input name="nik" value="{{ old('nik', $mentor->nik) }}" class="mt-2 w-full rounded-2xl border border-line bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/15">
                    </label>
                </div>
            </section>

            <section class="rounded-3xl border border-line bg-[#f4f8f6] p-5 md:p-6">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-primary-dark shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">work</span>
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold">Profesional</h2>
                        <p class="mt-1 text-sm text-muted">Posisi dan unit bisnis tempat Anda membimbing.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Posisi</span>
                        <input name="position" value="{{ old('position', $mentor->position) }}" placeholder="Posisimu di kantor" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
                    </label>
                    <label>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Departemen / unit bisnis</span>
                        <select name="business_unit_id" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
                            <option value="">— Pilih —</option>
                            @foreach($businessUnits as $unit)
                                <option value="{{ $unit->id }}" @selected((string) old('business_unit_id', $mentor->business_unit_id) === (string) $unit->id)>{{ $unit->name }} · {{ $unit->department?->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="md:col-span-2">
                        <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Keahlian (pisahkan dengan koma)</span>
                        <input name="expertise" value="{{ old('expertise', implode(', ', $mentor->expertise ?? [])) }}" placeholder="Product Analytics, Machine Learning" class="mt-2 w-full rounded-2xl border border-line bg-white px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
                    </label>
                </div>
            </section>

            <div class="flex justify-end">
                <button class="rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">Simpan profil</button>
            </div>
        </form>
    </section>
</div>
@endsection
