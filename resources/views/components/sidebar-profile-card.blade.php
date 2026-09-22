@php
    $user = auth()->user();
    $participant = $user->participant;
    $parts = collect(preg_split('/\s+/', trim($user->name)))->filter()->values();
    $initials = $parts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $avatar = filled($user->avatar) ? $user->avatar : null;
    $nidn = $participant?->nidn;
    $prodi = $participant?->study_program;
    $faculty = $participant?->faculty;
    $profileReady = filled($prodi) && filled($faculty);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-line bg-white p-4 shadow-sm']) }}>
    <div class="flex flex-col items-center text-center">
        @if($avatar)
            <img
                src="{{ $avatar }}"
                alt=""
                width="72"
                height="72"
                class="h-[72px] w-[72px] rounded-full object-cover ring-2 ring-line"
                referrerpolicy="no-referrer"
            >
        @else
            <span class="flex h-[72px] w-[72px] items-center justify-center rounded-full bg-primary/15 text-lg font-semibold text-primary-dark ring-2 ring-line">
                {{ $initials }}
            </span>
        @endif

        <p class="mt-3 text-sm font-semibold leading-snug text-ink">{{ $user->name }}</p>
        <p class="mt-0.5 text-xs text-muted">{{ $nidn ?: 'NIDN belum diisi' }}</p>

        <span @class([
            'mt-2 inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold',
            'bg-primary/15 text-primary-dark' => $profileReady,
            'bg-amber-50 text-amber-800' => ! $profileReady,
        ])>
            {{ $profileReady ? 'Profil lengkap' : 'Lengkapi profil' }}
        </span>
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
