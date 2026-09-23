@props(['application' => null, 'caption' => null])

@php
    $steps = $application
        ? $application->approvalSteps()
        : [
            ['key' => 'dosen', 'label' => 'Dosen mengajukan', 'actor' => 'Dosen', 'done' => false, 'current' => true],
            ['key' => 'admin_review', 'label' => 'Tinjauan admin', 'actor' => 'Admin', 'done' => false, 'current' => false],
            ['key' => 'mentor', 'label' => 'Persetujuan mentor', 'actor' => 'Mentor', 'done' => false, 'current' => false],
            ['key' => 'admin_final', 'label' => 'Pengesahan admin', 'actor' => 'Admin', 'done' => false, 'current' => false],
            ['key' => 'user', 'label' => 'Hasil ke dosen', 'actor' => 'Dosen', 'done' => false, 'current' => false],
        ];
    $viewUrl = $application
        ? route('participant.applications.edit', $application)
        : null;
    $canViewData = $application
        && auth()->check()
        && auth()->user()->isParticipant()
        && $application->participant_id === auth()->user()->participant?->id;
    $rejected = $application && $application->status === 'rejected';
@endphp

@if($caption)
    <p class="mb-4 text-sm leading-6 text-muted">{{ $caption }}</p>
@endif

<ol class="flex flex-col gap-5 sm:grid sm:grid-cols-5 sm:gap-0">
    @foreach($steps as $index => $step)
        @php
            $done = $step['done'];
            $current = $step['current'];
            $isViewable = $canViewData && ($done || $current) && $step['key'] === 'dosen';
            $linkDone = $index > 0 && $steps[$index - 1]['done'];
            $stepRejected = $rejected && $step['key'] === 'user';

            $circle = match (true) {
                $stepRejected => 'border-red-300 bg-red-50 text-red-600',
                $current => 'border-primary bg-primary text-white ring-4 ring-primary/20',
                $done => 'border-emerald-500 bg-emerald-500 text-white',
                default => 'border-line bg-white text-muted',
            };
            $actorClass = match (true) {
                $stepRejected => 'text-red-500',
                $current => 'text-primary-dark',
                $done => 'text-emerald-600',
                default => 'text-muted/70',
            };
            $labelClass = match (true) {
                $stepRejected => 'text-red-600',
                $current => 'text-ink',
                $done => 'text-ink',
                default => 'text-muted',
            };
            $dimmed = (!$done && !$current);
        @endphp
        <li class="relative flex gap-3 sm:flex-col sm:items-center sm:px-1 sm:text-center" @if($current) aria-current="step" @endif>
            @if($index > 0)
                <span aria-hidden="true" class="absolute left-5 top-10 h-[calc(100%-2.75rem)] w-px -translate-x-1/2 {{ $linkDone ? 'bg-emerald-300' : 'bg-line' }} sm:-left-1/2 sm:top-5 sm:h-px sm:w-full sm:translate-x-0"></span>
            @endif

            <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 {{ $circle }}">
                @if($current)
                    <span class="text-sm font-bold">{{ $index + 1 }}</span>
                    <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 animate-pulse rounded-full bg-primary ring-2 ring-white"></span>
                @elseif($stepRejected)
                    <span class="material-symbols-outlined text-[18px]">close</span>
                @elseif($done)
                    <span class="material-symbols-outlined text-[18px]">check</span>
                @else
                    <span class="text-sm font-bold">{{ $index + 1 }}</span>
                @endif
            </div>

            <div class="min-w-0 sm:mt-2 {{ $dimmed ? 'opacity-80' : '' }}">
                <p class="text-[10px] font-semibold uppercase tracking-[0.14em] {{ $actorClass }}">{{ $index + 1 }}. {{ $step['actor'] }}</p>
                <p class="mt-0.5 text-sm font-semibold leading-snug {{ $labelClass }}">{{ $step['label'] }}</p>

                @if($isViewable)
                    <a href="{{ $viewUrl }}" title="Lihat data yang dikirim" class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary-dark transition hover:bg-primary/20">
                        Lihat data
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </a>
                @elseif($current)
                    <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-1 text-[11px] font-semibold text-primary-dark">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-primary"></span>
                        Sedang berjalan
                    </span>
                @elseif($stepRejected)
                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-semibold text-red-600">Ditolak</span>
                @elseif($done)
                    <span class="mt-1.5 inline-block text-[11px] font-medium text-emerald-600">Selesai</span>
                @else
                    <span class="mt-1.5 inline-block text-[11px] font-medium text-muted/60">Menunggu</span>
                @endif
            </div>
        </li>
    @endforeach
</ol>