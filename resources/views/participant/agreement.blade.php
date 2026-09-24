@extends('layouts.app')
@section('title', 'Agreement')
@section('content')
<h1 class="text-2xl font-semibold">Perjanjian Magang Dosen</h1>
@unless($program)
    <x-empty class="mt-6" title="Agreement belum tersedia">Agreement dibuat setelah matching disetujui.</x-empty>
@else
<p class="mt-2 text-sm text-muted">Program tidak menjadi ACTIVE sebelum agreement berstatus AGREED.</p>
<div class="mt-4 flex flex-wrap items-center gap-3">
    <x-badge :status="$agreement?->status ?? 'draft'" />
    @if($agreement?->revision_note)
        <p class="text-sm text-amber-700">Revisi: {{ $agreement->revision_note }}</p>
    @endif
</div>
@if($program->mentor?->user)
    @php
        $mentorPhone = preg_replace('/\D+/', '', (string) $program->mentor->user->phone);
        if (str_starts_with($mentorPhone, '0')) {
            $mentorPhone = '62'.substr($mentorPhone, 1);
        }
    @endphp
    <div class="mt-5 flex flex-col gap-4 rounded-2xl border border-[#cde8dc] bg-[#f1fbf6] p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-primary text-white">
                <span class="material-symbols-outlined">support_agent</span>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary-dark">Mentor Anda</p>
                <p class="mt-0.5 font-semibold">{{ $program->mentor->user->name }}</p>
                <p class="text-sm text-muted">{{ $program->mentor->position ?: 'Mentor industri' }}</p>
            </div>
        </div>
        @if($mentorPhone)
            <a href="https://wa.me/{{ $mentorPhone }}" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#159447] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#107c3a]">
                <span class="material-symbols-outlined text-[19px]">chat</span>
                Hubungi via WhatsApp
            </a>
        @else
            <p class="text-sm text-muted">Nomor WhatsApp mentor belum tersedia.</p>
        @endif
    </div>
@endif
<div class="mt-6 grid gap-4 text-sm md:grid-cols-2">
    @foreach([
        'Peserta' => $program->participant->user->name,
        'Program Studi' => $program->participant->study_program,
        'Unit Bisnis' => $program->department->name,
        'Departemen' => $program->businessUnit->name,
        'Mentor' => $program->mentor->user->name,
        'Periode' => ($program->start_date?->format('d M Y') ?? '60 hari').' – '.($program->end_date?->format('d M Y') ?? ''),
    ] as $label => $value)
        <div class="rounded-xl border border-line bg-white p-4"><p class="text-xs text-muted">{{ $label }}</p><p class="mt-1 font-medium">{{ $value }}</p></div>
    @endforeach
</div>
@if($agreement && in_array($agreement->status, ['draft', 'revision'], true))
<form method="POST" class="mt-6 space-y-4 rounded-2xl border border-line bg-white p-6">
    @csrf
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Tujuan bersama
        <textarea name="objective" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('objective', $agreement->objective) }}</textarea>
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Aktivitas peserta
        <textarea name="activities" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('activities', $agreement->activities) }}</textarea>
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Problem / opportunity statement
        <textarea name="problem_statement" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('problem_statement', $agreement->problem_statement) }}</textarea>
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Main output
        <input name="main_output" value="{{ old('main_output', $agreement->main_output) }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>
    </label>
    <div class="grid gap-4 md:grid-cols-2">
        <label class="text-xs font-semibold uppercase tracking-wide text-muted">Manfaat dosen / TSU
            <textarea name="participant_benefit" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('participant_benefit', $agreement->participant_benefit) }}</textarea>
        </label>
        <label class="text-xs font-semibold uppercase tracking-wide text-muted">Manfaat unit bisnis
            <textarea name="business_benefit" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('business_benefit', $agreement->business_benefit) }}</textarea>
        </label>
    </div>
    @php $indicators = old('success_indicators', $agreement->success_indicators ?? ['','','']); @endphp
    @for($i = 0; $i < 3; $i++)
        <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Success indicator {{ $i+1 }}
            <input name="success_indicators[]" value="{{ $indicators[$i] ?? '' }}" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" @required($i === 0)>
        </label>
    @endfor
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Potensi kolaborasi
        <textarea name="collaboration_potential" rows="2" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm">{{ old('collaboration_potential', $agreement->collaboration_potential) }}</textarea>
    </label>
    <x-signature-pad
        name="participant_signature"
        label="Tanda tangan dosen"
        hint="Tanda tangan ini menjadi persetujuan pihak pertama pada surat perjanjian."
        :required="true"
        :value="old('participant_signature', $agreement->participant_signature)"
    />
    @error('participant_signature')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white">Ajukan persetujuan mentor</button>
</form>
@else
    <div class="mt-6 space-y-3 rounded-2xl border border-line bg-white p-6 text-sm">
        <p><b>Tujuan:</b> {{ $agreement?->objective }}</p>
        <p><b>Aktivitas:</b> {{ $agreement?->activities }}</p>
        <p><b>Problem:</b> {{ $agreement?->problem_statement }}</p>
        <p><b>Output:</b> {{ $agreement?->main_output }}</p>
        <p>Persetujuan peserta: {{ $agreement?->participant_approved_at?->format('d M Y H:i') ?? '—' }}</p>
        <p>Persetujuan mentor: {{ $agreement?->mentor_approved_at?->format('d M Y H:i') ?? '—' }}</p>
        @if($agreement?->status === 'agreed')
            <a href="{{ route('participant.agreement.print') }}" target="_blank" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 font-semibold text-white">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Cetak / Simpan PDF
            </a>
        @endif
    </div>
@endif
@endif
@endsection
