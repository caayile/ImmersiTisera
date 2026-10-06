@extends('layouts.app')
@section('title', 'Sertifikat')
@section('content')
<div>
    <h1 class="text-2xl font-semibold">Sertifikat Saya</h1>
    <p class="mt-1 text-sm text-muted">Sertifikat muncul otomatis setelah mentor menerbitkannya.</p>
</div>
<div class="mt-6 space-y-4">
    @forelse($certificates as $certificate)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="font-semibold">{{ $certificate->number }}</p>
                    <p class="mt-1 text-sm text-muted">
                        {{ $certificate->program->businessUnit?->name ?? '-' }}
                        · {{ $certificate->program->department?->name ?? '-' }}
                    </p>
                    <p class="mt-1 text-xs text-muted">Diterbitkan {{ $certificate->issued_at?->format('d M Y') ?? '-' }}</p>
                </div>
                <a href="{{ route('participant.certificates.print', $certificate) }}" target="_blank" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Lihat / Cetak</a>
            </div>
        </article>
    @empty
        <x-empty title="Belum ada sertifikat" class="mt-6">Sertifikat akan muncul di sini setelah mentor menerbitkannya.</x-empty>
    @endforelse
</div>
@endsection
