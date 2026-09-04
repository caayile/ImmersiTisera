@extends('layouts.public')
@section('title', 'Berita')
@section('content')
<div class="bg-bg py-12">
    <div class="mx-auto max-w-6xl px-5">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-dark">Berita Imersi</p>
        <h1 class="mt-2 text-3xl font-semibold">Semua berita</h1>
        <p class="mt-2 max-w-2xl text-muted">Pengumuman gelombang, unit bisnis, panduan program, dan kolaborasi dosen × industri.</p>

        <div class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($items as $item)
                <article class="flex flex-col overflow-hidden rounded-2xl border border-line bg-white">
                    <div class="flex h-36 items-end bg-gradient-to-br from-[#16352c] to-primary p-4">
                        <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">{{ $item->category }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <p class="text-xs font-semibold text-primary-dark">{{ $item->published_at?->translatedFormat('d M Y') }}</p>
                        <h2 class="mt-2 text-lg font-semibold leading-snug">{{ $item->title }}</h2>
                        <p class="mt-2 flex-1 text-sm leading-6 text-muted">{{ $item->excerpt }}</p>
                        <a href="{{ route('news.show', $item) }}" class="mt-4 inline-flex text-sm font-semibold text-primary-dark">Baca selengkapnya →</a>
                    </div>
                </article>
            @empty
                <p class="text-muted md:col-span-3">Belum ada berita terbit.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $items->links() }}</div>
    </div>
</div>
@endsection
