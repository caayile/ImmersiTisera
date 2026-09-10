@php
    $user = auth()->user();
    $parts = collect(preg_split('/\s+/', trim($user->name)))->filter()->values();
    $initials = $parts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $titles = ['Dr.', 'Dra.', 'Prof.', 'Ir.'];
    $shortName = $parts->isEmpty()
        ? $user->name
        : (in_array($parts[0], $titles, true) ? $parts->take(2)->implode(' ') : $parts[0]);

    $items = [
        ['Profil', route('profile.public'), 'person'],
    ];

    if ($user->isParticipant()) {
        $items[] = ['Riwayat Pendaftaran', route('participant.applications'), 'history'];
        $items[] = ['Logbook', route('participant.logbooks'), 'menu_book'];
    } elseif ($user->isMentor()) {
        $items[] = ['Pendaftaran', route('mentor.applications'), 'history'];
        $items[] = ['Logbook', route('mentor.logbooks'), 'menu_book'];
    } else {
        $items[] = ['Dasbor', route('admin.dashboard'), 'space_dashboard'];
    }
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }} x-data="{ menu: false }" @click.outside="menu = false">
    <button
        type="button"
        @click="menu = ! menu"
        class="inline-flex max-w-48 items-center gap-2 rounded-full bg-[#16352c] py-1 pl-1 pr-3 text-left text-white"
        aria-haspopup="true"
        :aria-expanded="menu.toString()"
    >
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-xs font-semibold text-primary-dark">{{ $initials }}</span>
        <span class="hidden min-w-0 truncate text-sm font-medium sm:block">{{ $shortName }}</span>
        <span class="material-symbols-outlined text-[18px] text-white/80">expand_more</span>
    </button>

    <div
        x-show="menu"
        x-cloak
        x-transition
        class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-2xl bg-[#16352c] py-3 text-white shadow-xl"
    >
        <div class="border-b border-white/10 px-4 pb-3">
            <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
            <p class="mt-0.5 truncate text-xs text-white/65">{{ $user->email }}</p>
        </div>
        <div class="py-1">
            @foreach($items as [$label, $url, $icon])
                <a href="{{ $url }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-white/90 transition hover:bg-white/10">
                    <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 pt-1">
            @csrf
            <button class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-white/90 transition hover:bg-white/10">
                <span class="material-symbols-outlined text-[18px]">logout</span>
                Keluar
            </button>
        </form>
    </div>
</div>
