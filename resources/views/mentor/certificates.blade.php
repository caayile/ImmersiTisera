@extends('layouts.app')
@section('title', 'Sertifikat')
@section('content')
<div>
    <h1 class="text-2xl font-semibold">Sertifikat</h1>
    <p class="mt-1 text-sm text-muted">Simpan tanda tangan Anda, lalu terbitkan sertifikat agar muncul di akun peserta.</p>
</div>

<article class="mt-6 rounded-2xl border border-line bg-white p-5">
    <h2 class="font-semibold">Tanda tangan mentor</h2>
    <p class="mt-1 text-sm text-muted">Tanda tangan ini dipakai otomatis saat menerbitkan sertifikat peserta.</p>
    @if(filled($mentor?->certificate_signature))
        <div class="mt-4 rounded-xl border border-line bg-bg/50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-muted">Tanda tangan tersimpan</p>
            <img src="{{ $mentor->certificate_signature }}" alt="Tanda tangan mentor" class="mt-3 h-20 object-contain">
        </div>
    @endif
    <form method="POST" action="{{ route('mentor.certificates.signature') }}" class="mt-4 space-y-3">
        @csrf
        <x-signature-pad
            name="certificate_signature"
            label="Gambar ulang tanda tangan"
            hint="Wajib diisi untuk menyimpan atau memperbarui tanda tangan."
            :required="true"
            :value="old('certificate_signature')"
        />
        @if ($errors->has('certificate_signature'))
            <p class="text-sm text-red-600">{{ $errors->first('certificate_signature') }}</p>
        @endif
        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan tanda tangan</button>
    </form>
</article>

<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white">
    <div class="border-b border-line px-4 py-3">
        <h2 class="font-semibold">Terbitkan ke peserta</h2>
        <p class="mt-0.5 text-sm text-muted">Sertifikat otomatis memakai data program dan tanda tangan yang sudah disimpan.</p>
    </div>
    @forelse($programs as $program)
        <div class="flex flex-wrap items-center gap-3 border-b border-line px-4 py-3 text-sm last:border-0">
            <span class="min-w-0 flex-1">
                <span class="block truncate font-semibold">{{ $program->participant->user->name }}</span>
                <span class="mt-0.5 block truncate text-xs text-muted">{{ $program->businessUnit?->name ?? '-' }} · {{ $program->department?->name ?? '-' }}</span>
            </span>
            @if($program->certificate?->isIssued())
                <span class="rounded-full bg-primary/15 px-2.5 py-1 text-[11px] font-semibold text-primary-dark">{{ $program->certificate->number }}</span>
                <a href="{{ route('mentor.certificates.print', $program->certificate) }}" target="_blank" class="rounded-lg border border-line px-3 py-2 text-xs font-semibold text-primary-dark">Lihat</a>
            @else
                <form method="POST" action="{{ route('mentor.certificates.issue', $program) }}">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-lg bg-primary px-4 py-2 text-xs font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(! filled($mentor?->certificate_signature))
                    >
                        Terbitkan sertifikat
                    </button>
                </form>
            @endif
        </div>
    @empty
        <x-empty title="Belum ada program aktif/selesai" />
    @endforelse
</div>
@endsection
