@extends('layouts.app')
@section('title', 'Notifikasi')
@section('content')
<h1 class="text-2xl font-semibold">Notifikasi</h1>
<div class="mt-6 space-y-3">
    @forelse($notifications as $item)
        <a href="{{ $item->data['url'] ?? '#' }}" class="block rounded-2xl border border-line bg-white p-4">
            <p class="font-medium">{{ $item->data['title'] ?? 'Notifikasi' }}</p>
            <p class="mt-1 text-sm text-muted">{{ $item->data['message'] ?? '' }}</p>
            <p class="mt-2 text-xs text-muted">{{ $item->created_at->diffForHumans() }}</p>
        </a>
    @empty
        <x-empty title="Tidak ada notifikasi" />
    @endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
