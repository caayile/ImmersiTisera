@extends('layouts.app')
@section('title', 'Matching')
@section('content')
<h1 class="text-2xl font-semibold">Pendaftaran & pencocokan</h1>
<p class="mt-1 text-sm text-muted">Alur: dosen mengajukan → admin meninjau → mentor menyetujui → admin mengesahkan → dosen menerima hasil.</p>
@if($errors->any())
    <p class="mt-4 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700">Gagal menyimpan: {{ $errors->first() }}</p>
@endif
<div class="mt-6 space-y-4">
    @forelse($applications as $application)
        <article class="rounded-2xl border border-line bg-white p-5" x-data="{ open: false }">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <button type="button" @click="open = ! open" class="flex min-w-0 flex-1 items-center gap-3 text-left">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/15 text-primary-dark"><span class="material-symbols-outlined">person_search</span></span>
                    <span class="min-w-0">
                        <span class="block truncate font-semibold">{{ $application->participant->user->name }}</span>
                        <span class="mt-0.5 block truncate text-xs text-muted">{{ $application->department->name }} · {{ $application->businessUnit->name }}</span>
                    </span>
                    <span class="material-symbols-outlined ml-auto text-muted" x-text="open ? 'expand_less' : 'expand_more'"></span>
                </button>
                <div class="flex items-center gap-2"><x-badge :status="$application->status" /></div>
            </div>
            <div x-show="open" x-cloak x-transition>
                <p class="mt-4 text-xs text-muted">{{ $application->letter_number ?? 'Tanpa nomor surat' }} · {{ $application->currentStageLabel() }} · skor {{ $application->match_score }}%</p>
                @if($application->relevance_warning)
                    <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">Warning: kompetensi/prodi peserta kurang relevan dengan unit yang dipilih.</p>
                @endif
                <div class="mt-4"><x-approval-flow :application="$application" /></div>
                <div class="mt-4"><x-approval-letter :application="$application" /></div>
            @if($application->isAwaitingAdmin())
            <form method="POST" action="{{ route('admin.matching.update', $application) }}" class="mt-4 grid gap-3 md:grid-cols-4" onsubmit="if (this.dataset.submitted === '1') { return false; } this.dataset.submitted = '1';">
                @csrf
                <select name="business_unit_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected($unit->id === $application->business_unit_id)>{{ $unit->department->name }} / {{ $unit->name }}</option>
                    @endforeach
                </select>
                <select name="mentor_id" class="rounded-lg border border-line px-3 py-2 text-sm">
                    @foreach($mentors as $mentor)
                        <option value="{{ $mentor->id }}" @selected($mentor->id === $application->mentor_id)>{{ $mentor->user->name }}</option>
                    @endforeach
                </select>
                <input name="matching_notes" placeholder="{{ $application->status === 'waiting_admin' ? 'Catatan pengesahan' : 'Catatan tinjauan' }}" class="rounded-lg border border-line px-3 py-2 text-sm">
                <div class="flex flex-wrap gap-2">
                    <button name="status" value="approved" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">
                        {{ $application->status === 'waiting_admin' ? 'Sahkan' : 'Teruskan ke mentor' }}
                    </button>
                    <button name="status" value="revision" class="rounded-lg border border-line px-4 py-2 text-sm">Revisi</button>
                </div>
            </form>
            @endif
            </div>
        </article>
    @empty
        <x-empty title="Tidak ada pengajuan" />
    @endforelse
</div>
@endsection
