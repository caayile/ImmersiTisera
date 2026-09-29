@extends('layouts.app')
@section('title', 'Pendaftaran')
@section('content')
<h1 class="text-2xl font-semibold">Pendaftaran & surat persetujuan</h1>
<p class="mt-1 text-sm text-muted">Tinjau surat yang diteruskan admin. Minta revisi ke dosen bila perlu, atau buka halaman tanda tangan untuk menyetujui.</p>
<div class="mt-6 space-y-4">
    @forelse($applications as $application)
        <article class="rounded-2xl border border-line bg-white p-5" x-data="{ open: false }">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <button type="button" @click="open = ! open" class="flex min-w-0 flex-1 items-center gap-3 text-left">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/15 text-primary-dark"><span class="material-symbols-outlined">person_search</span></span>
                    <span class="min-w-0"><span class="block truncate font-semibold">{{ $application->participant->user->name }}</span><span class="mt-0.5 block truncate text-xs text-muted">{{ $application->department->name }} · {{ $application->businessUnit->name }}</span></span>
                    <span class="material-symbols-outlined ml-auto text-muted" x-text="open ? 'expand_less' : 'expand_more'"></span>
                </button>
                <div class="flex flex-wrap items-center gap-2">
                    <x-badge :status="$application->status" />
                </div>
            </div>
            <div x-show="open" x-cloak x-transition>
                <p class="mt-4 text-xs text-muted">{{ $application->letter_number ?? 'Tanpa nomor surat' }} · {{ $application->currentStageLabel() }}</p>
                <div class="mt-4"><x-approval-flow :application="$application" /></div>
                @if($application->revision_note && $application->status === 'revision')
                    <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900">Menunggu dosen memperbaiki: {{ $application->revision_note }}</p>
                @endif
                <a href="{{ route('mentor.applications.show', $application) }}" class="mt-4 inline-flex items-center gap-1 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
                    @if($application->isAwaitingMentor()) Tinjau pendaftaran @else Lihat surat @endif
                </a>
            </div>
        </article>
    @empty
        <x-empty title="Belum ada pendaftaran" />
    @endforelse
</div>
@endsection
