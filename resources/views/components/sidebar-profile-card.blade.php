@php
    $user = auth()->user();
    $participant = $user->participant;
    $parts = collect(preg_split('/\s+/', trim($user->name)))->filter()->values();
    $initials = $parts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $avatar = $user->avatarUrl();
    $nidn = $participant?->nidn;
    $prodi = $participant?->study_program;
    $faculty = $participant?->faculty;
    $profileReady = filled($prodi) && filled($faculty);
@endphp

<div {{ $attributes->merge(['class' => 'relative rounded-3xl border border-[#d7e8e0] bg-white px-4 pb-4 pt-6 shadow-sm']) }}>
    <span @class([
        'absolute -top-3 left-4 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold',
        'bg-[#e7f8ef] text-emerald-700' => $profileReady,
        'bg-amber-50 text-amber-800' => ! $profileReady,
    ])>
        <span @class(['h-2 w-2 shrink-0 rounded-full', 'bg-emerald-500' => $profileReady, 'bg-amber-500' => ! $profileReady])></span>
        {{ $profileReady ? 'Profil Lengkap' : 'Lengkapi profil' }}
    </span>

    <div class="flex items-center gap-3.5 pt-2 text-left">
        @if($avatar)
            <img
                src="{{ $avatar }}"
                alt=""
                width="68"
                height="68"
                class="h-[68px] w-[68px] shrink-0 rounded-full object-cover"
                referrerpolicy="no-referrer"
            >
        @else
            <span class="flex h-[68px] w-[68px] shrink-0 items-center justify-center rounded-full bg-primary/15 text-lg font-semibold text-primary-dark">
                {{ $initials }}
            </span>
        @endif

        <div class="min-w-0">
            <p class="truncate text-lg font-bold leading-tight text-ink">{{ $user->name }}</p>
            <p class="mt-1 truncate text-base font-semibold text-ink">{{ $nidn ?: 'NIDN belum diisi' }}</p>
        </div>
    </div>

    <dl class="mt-4 space-y-3.5 border-t border-[#e5eeea] pt-4 text-left">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined mt-0.5 text-[22px] text-[#7b8682]">school</span>
            <div class="min-w-0">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#8b9692]">Prodi</dt>
                <dd class="mt-0.5 text-base font-bold text-ink">{{ $prodi ?: 'Belum diisi' }}</dd>
            </div>
        </div>
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined mt-0.5 text-[22px] text-[#7b8682]">account_balance</span>
            <div class="min-w-0">
                <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#8b9692]">Fakultas</dt>
                <dd class="mt-0.5 text-base font-bold text-ink">{{ $faculty ?: 'Belum diisi' }}</dd>
            </div>
        </div>
    </dl>
</div>
