@extends('layouts.app')
@section('title', 'Tanda tangan surat')
@section('content')
@php
    $mentorIndicators = $application->normalizedSuccessIndicators();
    if (count($mentorIndicators) < 2) {
        $mentorIndicators = array_pad($mentorIndicators, 2, '');
    }
@endphp

<div class="mb-4">
    <a href="{{ route('mentor.applications') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-dark">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        Kembali ke daftar
    </a>
</div>

<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">Persetujuan mentor</p>
        <h1 class="mt-1 text-2xl font-semibold">Tinjau & tandatangani surat</h1>
        <p class="mt-1 text-sm text-muted">{{ $application->participant->user->name }} · {{ $application->businessUnit->name }}</p>
    </div>
    <x-badge :status="$application->status" />
</div>

<p class="mt-2 text-sm text-muted">{{ $application->letter_number ?? 'Tanpa nomor surat' }} · {{ $application->currentStageLabel() }}</p>

<div class="mt-6">
    <x-approval-flow :application="$application" />
</div>

<div class="mt-6">
    <x-approval-letter :application="$application" />
</div>

@if($application->isAwaitingMentor())
    <section class="mt-6 rounded-2xl border border-line bg-white p-5 md:p-6">
        <h2 class="text-lg font-semibold">Keputusan mentor</h2>
        <p class="mt-1 text-sm text-muted">Jika belum puas, minta revisi ke dosen. Setelah dosen memperbaiki, surat kembali ke halaman ini untuk ditinjau dan ditandatangani.</p>

        <form
            method="POST"
            action="{{ route('mentor.applications.review', $application) }}"
            class="mt-5 space-y-4"
            onsubmit="
                if (this.dataset.submitted === '1') { return false; }
                const decision = event.submitter && event.submitter.value;
                if (decision === 'approved') {
                    const signature = this.querySelector('[name=mentor_signature]');
                    if (! signature || ! signature.value) {
                        alert('Tanda tangan digital wajib diisi saat menyetujui.');
                        return false;
                    }
                }
                if (decision === 'revision') {
                    const note = this.querySelector('[name=mentor_note]');
                    if (! note || note.value.trim().length < 10) {
                        alert('Tulis komentar revisi minimal 10 karakter.');
                        note && note.focus();
                        return false;
                    }
                }
                this.dataset.submitted = '1';
            "
        >
            @csrf

            <div class="rounded-xl border border-line bg-bg p-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 6 — Success Indicators</p>
                <p class="mt-1 text-xs text-muted">Anda boleh mengusulkan perbaikan teks indikator. Komentar revisi bisa dikirim berulang kali sampai Anda puas.</p>
                <div class="mt-3 grid gap-2">
                    @foreach($mentorIndicators as $index => $indicator)
                        <label class="flex items-center gap-2 text-sm">
                            <span class="w-4 text-xs font-semibold text-muted">{{ $index + 1 }}.</span>
                            <input name="success_indicators[]" value="{{ $indicator }}" maxlength="255" placeholder="Indikator {{ $index + 1 }}" class="w-full rounded-lg border border-line bg-white px-3 py-2 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
                        </label>
                    @endforeach
                </div>
                @if($application->indicator_feedback)
                    <p class="mt-2 text-xs text-muted"><b>Feedback dosen:</b> {{ $application->indicator_feedback }}</p>
                @endif
            </div>

            <label class="block">
                <span class="text-sm font-medium">Komentar untuk dosen*</span>
                <span class="mt-0.5 block text-xs text-muted">Wajib saat minta revisi. Dosen akan menerima komentar ini dan bisa mengedit pernyataan lagi.</span>
                <textarea name="mentor_note" rows="4" placeholder="Tuliskan permintaan perbaikan untuk dosen" class="mt-2 w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">{{ old('mentor_note') }}</textarea>
                @error('mentor_note')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </label>

            <x-signature-pad
                name="mentor_signature"
                label="Tanda tangan mentor"
                hint="Coret di kotak atau unggah gambar. Wajib saat menyetujui surat persetujuan."
                :required="false"
                :value="old('mentor_signature')"
            />
            @error('mentor_signature')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line pt-4">
                <button
                    type="submit"
                    name="decision"
                    value="revision"
                    class="inline-flex items-center justify-center rounded-lg border border-amber-300 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-900 transition hover:bg-amber-100"
                >
                    Minta revisi ke dosen
                </button>
                <button
                    type="submit"
                    name="decision"
                    value="approved"
                    class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-dark"
                >
                    Setujui & tandatangani
                </button>
            </div>
        </form>
    </section>
@elseif($application->status === 'revision')
    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
        <p class="font-semibold">Menunggu perbaikan dosen</p>
        <p class="mt-1">{{ $application->revision_note ?: 'Dosen sedang memperbaiki pernyataan sesuai catatan Anda.' }}</p>
        <p class="mt-2 text-xs">Setelah dosen mengirim ulang, surat akan kembali ke sini untuk ditinjau dan ditandatangani.</p>
    </div>
@elseif($application->hasMentorSignature())
    <div class="mt-6 rounded-2xl border border-line bg-bg px-5 py-4 text-sm text-muted">
        <p class="font-semibold text-ink">Sudah ditandatangani</p>
        <p class="mt-1">Surat ini sudah Anda tandatangani{{ $application->mentor_signed_at ? ' pada '.$application->mentor_signed_at->format('d M Y H:i') : '' }}. Status saat ini: {{ $application->currentStageLabel() }}.</p>
    </div>
@endif
@endsection
