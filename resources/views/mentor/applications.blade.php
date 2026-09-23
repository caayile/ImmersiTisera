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
            @php $mentorIndicators = array_pad($application->normalizedSuccessIndicators(), 3, ''); @endphp
            <form method="POST" action="{{ route('mentor.applications.review', $application) }}" class="mt-4 space-y-3">
                @csrf
                <div class="rounded-xl border border-line bg-bg p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Bagian 6 — Success Indicators (dapat diedit mentor)</p>
                    <p class="mt-1 text-xs text-muted">Ubah teks indikator bila dirasa kurang, lalu pilih <b>Minta revisi</b> agar usulan terkirim kembali ke peserta beserta catatan Anda.</p>
                    <div class="mt-3 grid gap-2">
                        @for($i = 0; $i < 3; $i++)
                            <label class="flex items-center gap-2 text-sm">
                                <span class="w-4 text-xs font-semibold text-muted">{{ $i + 1 }}.</span>
                                <input name="success_indicators[]" value="{{ $mentorIndicators[$i] ?? '' }}" maxlength="255" placeholder="Indikator {{ $i + 1 }}{{ $i === 0 ? ' (wajib terisi saat revisi indikator)' : ' (opsional)' }}" class="w-full rounded-lg border border-line bg-white px-3 py-2 text-sm outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/15">
                            </label>
                        @endfor
                    </div>
                    @if($application->indicator_feedback)
                        <p class="mt-2 text-xs text-muted"><b>Feedback peserta:</b> {{ $application->indicator_feedback }}</p>
                    @endif
                </div>
                <div class="grid gap-3 md:grid-cols-[1fr_auto_auto_auto]">
                    <input name="mentor_note" placeholder="Catatan mentor atau permintaan revisi" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <button name="decision" value="revision" class="rounded-lg border border-line px-4 py-2 text-sm">Minta revisi</button>
                    <button name="decision" value="rejected" class="rounded-lg border border-line px-4 py-2 text-sm">Tolak</button>
                    <button name="decision" value="approved" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Setujui</button>
                </div>
            </form>
            @endif
        </article>
    @empty
        <x-empty title="Belum ada pendaftaran" />
    @endforelse
</div>
@endsection
