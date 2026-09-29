@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->count();
    $inbox = $user->notifications()->latest()->limit(5)->get();
    $index = route($user->notificationsRoute());
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }} x-data="{ panel: false, toast: {{ $unread ? 'true' : 'false' }} }" @click.outside="panel = false">
    @if($unread && $user->isParticipant() && $inbox->first())
        <div x-show="toast" x-transition class="fixed right-5 top-20 z-[60] w-[min(24rem,calc(100vw-2rem))] rounded-2xl border border-[#b8e4ce] bg-white p-4 shadow-2xl shadow-[#173d32]/15" role="status">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#e5f8ee] text-primary-dark"><span class="material-symbols-outlined text-[20px]">campaign</span></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold">{{ $inbox->first()->data['title'] ?? 'Notifikasi baru' }}</p>
                    <p class="mt-1 text-xs leading-5 text-muted">{{ $inbox->first()->data['message'] ?? '' }}</p>
                    <a href="{{ $inbox->first()->data['url'] ?? $index }}" class="mt-2 inline-flex text-xs font-semibold text-primary-dark">Buka detail</a>
                </div>
                <button type="button" @click="toast = false" class="text-muted hover:text-ink" aria-label="Tutup notifikasi"><span class="material-symbols-outlined text-[18px]">close</span></button>
            </div>
        </div>
    @endif
    <button
        type="button"
        @click="panel = ! panel"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-line bg-white text-ink hover:bg-bg"
        aria-haspopup="true"
        :aria-expanded="panel.toString()"
        aria-label="{{ $unread ? 'Notifikasi, '.$unread.' belum dibaca' : 'Notifikasi' }}"
    >
        <span class="material-symbols-outlined text-[22px]">notifications</span>
        @if($unread)
            <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-semibold leading-none text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
        @endif
    </button>

    <div
        x-show="panel"
        x-cloak
        x-transition
        class="absolute right-0 z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-line bg-white py-2 shadow-xl"
    >
        <div class="flex items-center justify-between px-4 py-2">
            <p class="text-sm font-semibold">Notifikasi</p>
            <a href="{{ $index }}" class="text-xs font-semibold text-primary-dark">Lihat semua</a>
        </div>
        @forelse($inbox as $item)
            <a href="{{ $item->data['url'] ?? $index }}" class="block border-t border-line px-4 py-3 {{ $item->unread() ? 'bg-primary/5' : '' }}">
                <p class="text-sm font-medium">{{ $item->data['title'] ?? 'Notifikasi' }}</p>
                <p class="mt-0.5 text-xs text-muted">{{ $item->data['message'] ?? '' }}</p>
            </a>
        @empty
            <p class="border-t border-line px-4 py-3 text-sm text-muted">Belum ada notifikasi.</p>
        @endforelse
    </div>
</div>
