@extends('layouts.app')
@section('title', 'Perjanjian')
@section('content')
<h1 class="text-2xl font-semibold">Perjanjian Magang Dosen</h1>
@unless($program)
    <x-empty class="mt-6" title="Perjanjian belum tersedia">Perjanjian dibuat setelah penempatan disetujui.</x-empty>
@else
<p class="mt-2 text-sm text-muted">Program baru dimulai setelah perjanjian disepakati.</p>
<div class="mt-4 flex flex-wrap items-center gap-3">
    <x-badge :status="$agreement?->status ?? 'draft'" />
    @if($agreement?->revision_note)
        <p class="text-sm text-amber-700">Revisi: {{ $agreement->revision_note }}</p>
    @endif
</div>
<div x-data="{ agreeOpen: false, signOpen: {{ old('_sign') && $errors->any() ? 'true' : 'false' }} }">
    <div class="mt-6 overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead>
                <tr class="border-b border-line text-sm font-semibold">
                    <th class="px-4 py-3">No.</th>
                    <th class="px-4 py-3">Nomor Surat</th>
                    <th class="px-4 py-3">Unit Bisnis</th>
                    <th class="px-4 py-3">Departemen</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-line last:border-0">
                    <td class="px-4 py-3 align-top">1</td>
                    <td class="px-4 py-3 align-top font-medium">{{ $agreement?->letter_number ?: '—' }}</td>
                    <td class="px-4 py-3 align-top">{{ $program->department?->name ?? '—' }}</td>
                    <td class="px-4 py-3 align-top">{{ $program->businessUnit?->name ?? '—' }}</td>
                    <td class="px-4 py-3 align-top">
                        <div class="flex flex-wrap gap-2">
                            @if($agreement && in_array($agreement->status, ['draft', 'revision'], true))
                                <button type="button" @click="signOpen = true" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white transition hover:bg-primary-dark">
                                    <span class="material-symbols-outlined text-[16px]">draw</span>
                                    Tandatangani
                                </button>
                            @endif
                            @if($agreement)
                                <button type="button" @click="agreeOpen = true" class="inline-flex items-center gap-1.5 rounded-lg border border-primary/30 bg-white px-3 py-2 text-xs font-semibold text-primary-dark transition hover:bg-primary/5">
                                    <span class="material-symbols-outlined text-[16px]">info</span>
                                    Detail informasi
                                </button>
                            @endif
                            @if($agreement?->status === 'agreed')
                                <a href="{{ route('participant.agreement.print') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white">
                                    <span class="material-symbols-outlined text-[16px]">print</span>
                                    Cetak / Simpan PDF
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($agreement)
        <div x-show="signOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="signOpen && 'flex'" @click.self="signOpen = false" @keydown.escape.window="signOpen = false" role="dialog" aria-modal="true" aria-label="Tanda tangani surat perjanjian">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white shadow-xl">
                <div class="flex items-center justify-between gap-3 border-b border-line p-5">
                    <div>
                        <h3 class="text-lg font-semibold">Tanda tangani surat perjanjian</h3>
                        <p class="mt-1 text-xs text-muted">Bubuhkan tanda tangan sebagai persetujuan pihak pertama, lalu simpan.</p>
                    </div>
                    <button type="button" @click="signOpen = false" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-line text-muted transition hover:text-ink" aria-label="Tutup">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('participant.agreement.sign') }}" class="space-y-4 p-5">
                    @csrf
                    <input type="hidden" name="_sign" value="1">
                    <x-signature-pad
                        name="participant_signature"
                        label="Tanda tangan dosen"
                        hint="Coret di kotak atau unggah gambar. Simpan untuk mengajukan ke mentor."
                        :required="true"
                        :value="old('participant_signature', $agreement->participant_signature)"
                    />
                    @if(old('_sign') && $errors->has('participant_signature'))
                        <p class="text-sm text-red-600">{{ $errors->first('participant_signature') }}</p>
                    @endif
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="signOpen = false" class="rounded-xl border border-line px-4 py-2.5 text-sm font-semibold text-muted">Batal</button>
                        <button class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @if($agreement)
        <div x-show="agreeOpen" x-cloak class="fixed inset-0 z-50 items-center justify-center bg-black/40 p-4" :class="agreeOpen && 'flex'" @click.self="agreeOpen = false" @keydown.escape.window="agreeOpen = false" role="dialog" aria-modal="true" aria-label="Detail perjanjian">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white shadow-xl">
                <div class="flex items-center justify-between gap-3 border-b border-line p-5">
                    <h3 class="text-lg font-semibold">Detail informasi perjanjian</h3>
                    <button type="button" @click="agreeOpen = false" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-line text-muted transition hover:text-ink" aria-label="Tutup">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-line text-sm font-semibold">
                                <th class="w-44 px-4 py-3">Aspek</th>
                                <th class="px-4 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                'Tujuan' => $agreement?->objective,
                                'Aktivitas' => $agreement?->activities,
                                'Masalah' => $agreement?->problem_statement,
                                'Hasil utama' => $agreement?->main_output,
                                'Persetujuan peserta' => $agreement?->participant_approved_at?->format('d M Y H:i') ?? '—',
                                'Persetujuan mentor' => $agreement?->mentor_approved_at?->format('d M Y H:i') ?? '—',
                            ] as $label => $value)
                                <tr class="border-b border-line last:border-0">
                                    <td class="px-4 py-3 align-top font-semibold">{{ $label }}</td>
                                    <td class="px-4 py-3 align-top text-muted">{{ $value ?: '—' }}</td>
                                </tr>
                            @endforeach
                            @if(filled($agreement?->letter_number))
                                <tr class="border-b border-line last:border-0">
                                    <td class="px-4 py-3 align-top font-semibold">Nomor surat</td>
                                    <td class="px-4 py-3 align-top text-muted">{{ $agreement->letter_number }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@if($agreement && in_array($agreement->status, ['draft', 'revision'], true))
<form id="form-perjanjian" method="POST" class="mt-6 space-y-4 rounded-2xl border border-line bg-white p-6">
    @csrf
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Tujuan bersama
        <textarea name="objective" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('objective', $agreement->objective) }}</textarea>
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Aktivitas peserta
        <textarea name="activities" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('activities', $agreement->activities) }}</textarea>
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Rumusan masalah atau peluang
        <textarea name="problem_statement" rows="3" class="mt-2 w-full rounded-lg border border-line px-4 py-2.5 text-sm" required>{{ old('problem_statement', $agreement->problem_statement) }}</textarea>
    </label>
    <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Hasil utama
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
        <label class="block text-xs font-semibold uppercase tracking-wide text-muted">Indikator keberhasilan {{ $i+1 }}
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
@endif
@endif
@endsection
