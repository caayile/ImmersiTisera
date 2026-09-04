@extends('layouts.app')
@section('title', 'Matching')
@section('content')
<h1 class="text-2xl font-semibold">Matching</h1>
<p class="mt-1 text-sm text-muted">Participant → Prodi → Kompetensi → Department → Unit Bisnis → Mentor</p>
<div class="mt-6 space-y-4">
    @forelse($applications as $application)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="font-medium">{{ $application->participant->user->name }} · {{ $application->participant->study_program }}</p>
                <x-badge :status="$application->status" />
            </div>
            <p class="mt-2 text-sm">{{ $application->department->name }} → {{ $application->businessUnit->name }} · skor {{ $application->match_score }}%</p>
            @if($application->relevance_warning)
                <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">Warning: kompetensi/prodi peserta kurang relevan dengan unit yang dipilih.</p>
            @endif
            <p class="mt-2 text-sm text-muted">{{ $application->motivation }}</p>
            @if($application->status === 'submitted')
            <form method="POST" action="{{ route('admin.matching.update', $application) }}" class="mt-4 grid gap-3 md:grid-cols-4">
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
                <input name="matching_notes" placeholder="Catatan matching" class="rounded-lg border border-line px-3 py-2 text-sm">
                <div class="flex gap-2">
                    <button name="status" value="approved" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Approve</button>
                    <button name="status" value="revision" class="rounded-lg border border-line px-4 py-2 text-sm">Revisi</button>
                    <button name="status" value="rejected" class="rounded-lg border border-line px-4 py-2 text-sm">Tolak</button>
                </div>
            </form>
            @endif
        </article>
    @empty
        <x-empty title="Tidak ada pengajuan" />
    @endforelse
</div>
@endsection
