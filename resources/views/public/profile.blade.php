@extends('layouts.public')
@section('title', 'Profil')
@section('content')
@php
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
@endphp
<div class="bg-bg py-12">
    <div class="mx-auto max-w-4xl px-5">
        <div class="overflow-hidden rounded-3xl border border-line bg-white">
            <div class="hero-grid px-6 py-8 md:px-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white text-2xl font-semibold text-primary-dark">{{ $initials }}</span>
                    <div class="text-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">{{ $user->roleLabel() }}</p>
                        <h1 class="mt-1 text-3xl font-semibold">{{ $user->name }}</h1>
                        <p class="mt-1 text-sm text-white/75">{{ $user->email }}</p>
                    </div>
                    @if($user->isParticipant())
                        <a href="{{ route('participant.profile') }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink sm:ml-auto">Ubah profil</a>
                    @else
                        <a href="{{ route($user->homeRoute()) }}" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-ink sm:ml-auto">Buka dasbor</a>
                    @endif
                </div>
            </div>

            <div class="grid gap-4 p-6 md:grid-cols-2 md:p-8">
                <div class="rounded-2xl bg-bg p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Nama</p>
                    <p class="mt-1 font-medium">{{ $user->name }}</p>
                </div>
                <div class="rounded-2xl bg-bg p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Peran</p>
                    <p class="mt-1 font-medium">{{ $user->roleLabel() }}</p>
                </div>
                <div class="rounded-2xl bg-bg p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Email</p>
                    <p class="mt-1 font-medium">{{ $user->email }}</p>
                </div>
                <div class="rounded-2xl bg-bg p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Telepon</p>
                    <p class="mt-1 font-medium">{{ $user->phone ?: 'Belum diisi' }}</p>
                </div>

                @if($user->isParticipant())
                    <div class="rounded-2xl bg-bg p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Fakultas / sekolah</p>
                        <p class="mt-1 font-medium">{{ $user->participant?->faculty ?: 'Belum diisi' }}</p>
                    </div>
                    <div class="rounded-2xl bg-bg p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Program studi</p>
                        <p class="mt-1 font-medium">{{ $user->participant?->study_program ?: 'Belum diisi' }}</p>
                    </div>
                    <div class="rounded-2xl bg-bg p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">NIP / NIDN</p>
                        <p class="mt-1 font-medium">{{ $user->participant?->nidn ?: 'Belum diisi' }}</p>
                    </div>
                    <div class="rounded-2xl bg-bg p-5 md:col-span-2">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Kompetensi</p>
                        <p class="mt-2 flex flex-wrap gap-2">
                            @forelse($user->participant?->competency ?? [] as $item)
                                <span class="rounded-full bg-primary/15 px-3 py-1 text-sm font-medium text-primary-dark">{{ $item }}</span>
                            @empty
                                <span class="text-sm text-muted">Belum diisi</span>
                            @endforelse
                        </p>
                    </div>
                    <div class="rounded-2xl bg-bg p-5 md:col-span-2">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Keahlian</p>
                        <p class="mt-2 flex flex-wrap gap-2">
                            @forelse($user->participant?->expertise ?? [] as $item)
                                <span class="rounded-full bg-white px-3 py-1 text-sm font-medium">{{ $item }}</span>
                            @empty
                                <span class="text-sm text-muted">Belum diisi</span>
                            @endforelse
                        </p>
                    </div>
                    @if($user->participant?->experience)
                        <div class="rounded-2xl bg-bg p-5 md:col-span-2">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Pengalaman</p>
                            <p class="mt-2 text-sm leading-6">{{ $user->participant->experience }}</p>
                        </div>
                    @endif
                @endif

                @if($user->isMentor())
                    <div class="rounded-2xl bg-bg p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Jabatan</p>
                        <p class="mt-1 font-medium">{{ $user->mentor?->position ?: 'Belum diisi' }}</p>
                    </div>
                    <div class="rounded-2xl bg-bg p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Departemen</p>
                        <p class="mt-1 font-medium">{{ $user->mentor?->department?->name ?: 'Belum diisi' }}</p>
                    </div>
                    <div class="rounded-2xl bg-bg p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Unit bisnis</p>
                        <p class="mt-1 font-medium">{{ $user->mentor?->businessUnit?->name ?: 'Belum diisi' }}</p>
                    </div>
                    <div class="rounded-2xl bg-bg p-5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Ketersediaan</p>
                        <p class="mt-1 font-medium">{{ $user->mentor?->availability ?: 'Belum diisi' }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($program)
            <article class="mt-6 rounded-3xl border border-line bg-white p-6 md:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">Penempatan imersi</p>
                <h2 class="mt-2 text-xl font-semibold">{{ $program->businessUnit?->name }}</h2>
                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Departemen</p>
                        <p class="mt-1 font-medium">{{ $program->department?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Mentor</p>
                        <p class="mt-1 font-medium">{{ $program->mentor?->user?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Status</p>
                        <p class="mt-1"><x-badge :status="$program->status" /></p>
                    </div>
                </div>
            </article>
        @endif
    </div>
</div>
@endsection
