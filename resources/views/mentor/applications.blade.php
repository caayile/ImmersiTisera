@extends('layouts.app')
@section('title', 'Pendaftaran')
@section('content')
<h1 class="text-2xl font-semibold">Pendaftaran & surat persetujuan</h1>
<p class="mt-1 text-sm text-muted">Tinjau surat yang diteruskan admin. Minta revisi ke dosen bila perlu, atau buka halaman tanda tangan untuk menyetujui.</p>
<div class="mt-6 space-y-4">
    @forelse($applications as $application)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium">{{ $application->participant->user->name }} · {{ $application->businessUnit->name }}</p>
                    <p class="mt-1 text-xs text-muted">{{ $application->letter_number ?? 'Tanpa nomor surat' }} · {{ $application->currentStageLabel() }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge :status="$application->status" />
                    <a href="{{ route('mentor.applications.show', $application) }}" class="inline-flex items-center gap-1 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                        @if($application->isAwaitingMentor())
                            Tinjau & tandatangani
                        @else
                            Lihat surat
                        @endif
                    </a>
                </div>
            </div>
            <div class="mt-4">
                <x-approval-flow :application="$application" />
            </div>
            @if($application->revision_note && $application->status === 'revision')
                <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Menunggu dosen memperbaiki: {{ $application->revision_note }}</p>
            @endif
        </article>
    @empty
        <x-empty title="Belum ada pendaftaran" />
    @endforelse
</div>
@endsection
