@extends('layouts.form')
@section('title', 'Surat Persetujuan')
@section('content')
<div class="mx-auto max-w-4xl">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">Pendaftaran</p>
            <h1 class="mt-1 text-2xl font-semibold">Status surat persetujuan</h1>
            <p class="mt-1 text-sm text-muted">{{ $application->currentStageLabel() }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($application->canBeRevisedByParticipant())
                <a href="{{ route('participant.applications.edit', $application) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Perbaiki form</a>
            @endif
            <a href="{{ route('participant.applications') }}" class="rounded-lg border border-line px-4 py-2 text-sm font-semibold">Semua pendaftaran</a>
        </div>
    </div>

    <div class="mt-6">
        <x-approval-flow :application="$application" />
    </div>

    <div class="mt-6">
        <x-approval-letter :application="$application" />
    </div>
</div>
@endsection
