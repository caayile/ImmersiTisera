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

<div {{ $attributes->merge(['class' => 'relative rounded-2xl border border-line bg-white p-4 shadow-sm']) }}>
    <span @class([
        'absolute left-4 top-4 inline-flex rounded-full px-1.5 py-px text-[9px] font-semibold',
        'bg-primary/15 text-primary-dark' => $profileReady,
        'bg-amber-50 text-amber-800' => ! $profileReady,
    ])>
        {{ $profileReady ? 'Profil lengkap' : 'Lengkapi profil' }}
    </span>
    <div class="flex items-center gap-3 pt-8 text-left">
        @if($avatar)
            <img
                src="{{ $avatar }}"
                alt=""
                width="56"
                height="56"
                class="h-14 w-14 shrink-0 rounded-full object-cover ring-2 ring-line"
                referrerpolicy="no-referrer"
            >
        @else
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-primary/15 text-base font-semibold text-primary-dark ring-2 ring-line">
                {{ $initials }}
            </span>
        @endif

        <div class="min-w-0">
            <p class="truncate text-sm font-semibold leading-snug text-ink">{{ $user->name }}</p>
            <p class="mt-0.5 truncate text-xs text-muted">{{ $nidn ?: 'NIDN belum diisi' }}</p>
        </div>
    </div>

    <dl class="mt-4 space-y-3 border-t border-line pt-4 text-left">
        <div class="flex items-start gap-2.5">
            <span class="material-symbols-outlined mt-0.5 text-[18px] text-muted">school</span>
            <div class="min-w-0">
                <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Prodi</dt>
                <dd class="mt-0.5 text-sm font-medium text-ink">{{ $prodi ?: 'Belum diisi' }}</dd>
            </div>
        </div>
        <div class="flex items-start gap-2.5">
            <span class="material-symbols-outlined mt-0.5 text-[18px] text-muted">account_balance</span>
            <div class="min-w-0">
                <dt class="text-[10px] font-semibold uppercase tracking-[0.12em] text-muted">Fakultas</dt>
                <dd class="mt-0.5 text-sm font-medium text-ink">{{ $faculty ?: 'Belum diisi' }}</dd>
            </div>
        </div>
    </dl>
</div>
