@extends('layouts.app')
@section('title', 'Pendaftaran')
@section('content')
<h1 class="text-2xl font-semibold">Pendaftaran & surat persetujuan</h1>
<p class="mt-1 text-sm text-muted">Tinjau setelah admin meneruskan. Keputusan Anda kembali ke admin untuk pengesahan.</p>
<div class="mt-6 space-y-4">
    @forelse($applications as $application)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $application->participant->user->name }} · {{ $application->businessUnit->name }}</p>
                <x-badge :status="$application->status" />
            </div>
            <p class="mt-1 text-xs text-muted">{{ $application->letter_number ?? 'Tanpa nomor surat' }} · {{ $application->currentStageLabel() }}</p>
            <div class="mt-4">
                <x-approval-flow :application="$application" />
            </div>
            <div class="mt-4">
                <x-approval-letter :application="$application" />
            </div>
            @if($application->isAwaitingMentor())
            <form method="POST" action="{{ route('mentor.applications.review', $application) }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_auto_auto_auto]">
                @csrf
                <input name="mentor_note" placeholder="Catatan mentor atau permintaan revisi" class="rounded-lg border border-line px-3 py-2 text-sm">
                <button name="decision" value="revision" class="rounded-lg border border-line px-4 py-2 text-sm">Minta revisi</button>
                <button name="decision" value="rejected" class="rounded-lg border border-line px-4 py-2 text-sm">Tolak</button>
                <button name="decision" value="approved" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Setujui</button>
            </form>
            @endif
        </article>
    @empty
        <x-empty title="Belum ada pendaftaran" />
    @endforelse
</div>
@endsection
