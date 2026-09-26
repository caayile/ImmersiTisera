@props(['log'])
@php
    $attendanceStyles = [
        'Izin' => 'bg-indigo-50 text-indigo-700',
        'Sakit' => 'bg-rose-50 text-rose-700',
        'Tanpa Keterangan' => 'bg-slate-100 text-slate-600',
    ];
@endphp
<article class="rounded-xl border border-line bg-white p-4 text-sm shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="font-semibold">{{ $log->program?->businessUnit?->name ?: 'Program magang dosen' }}</p>
            <p class="mt-0.5 text-xs text-muted">{{ $log->program?->department?->name ?: 'Unit bisnis belum ditentukan' }}</p>
            @if($log->activity)
                <p class="mt-0.5 text-xs text-muted">{{ $log->activity }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
            @if($log->attendance && $log->attendance !== 'Hadir')
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide {{ $attendanceStyles[$log->attendance] ?? 'bg-slate-100 text-slate-600' }}">{{ $log->attendance }}</span>
            @endif
            @if($log->status === 'approved')
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Terverifikasi</span>
            @endif
            <x-badge :status="$log->status" :label="\App\Support\Status::outputLabel($log->status)" />
        </div>
    </div>
    <div class="mt-3 space-y-3">
        <div>
            <p class="text-xs font-medium text-muted">Kehadiran</p>
            <p class="mt-1 rounded-lg border border-line bg-bg/60 px-4 py-2.5">{{ $log->attendance ?: 'Hadir' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-muted">Apa yang dilakukan</p>
            <p class="mt-1 rounded-lg border border-line bg-bg/60 px-4 py-2.5">{{ $log->what_i_did }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-muted">Apa yang dipelajari</p>
            <p class="mt-1 rounded-lg border border-line bg-bg/60 px-4 py-2.5">{{ $log->what_i_learned }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-muted">Apa yang ditemukan</p>
            <p class="mt-1 rounded-lg border border-line bg-bg/60 px-4 py-2.5">{{ $log->what_i_found }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-muted">Kendala</p>
            <p class="mt-1 rounded-lg border border-line bg-bg/60 px-4 py-2.5">{{ $log->value ?: '—' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-muted">Output hari ini</p>
            <p class="mt-1 rounded-lg border border-line bg-bg/60 px-4 py-2.5">{{ $log->next_action ?: '—' }}</p>
        </div>
        @if($log->mentor_feedback)
            <p class="text-primary-dark"><b>Feedback mentor:</b> {{ $log->mentor_feedback }}</p>
        @endif
    </div>
</article>
