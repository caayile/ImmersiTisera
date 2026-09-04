@extends('layouts.app')
@section('title', 'Berita')
@section('content')
<div class="flex flex-wrap items-end justify-between gap-3">
    <div>
        <h1 class="text-2xl font-semibold">Berita</h1>
        <p class="mt-1 text-sm text-muted">Tambah, ubah, atau hapus berita yang tampil di beranda dan halaman Berita.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.news') }}" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-2">
    @csrf
    <input name="title" placeholder="Judul berita" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2" required>
    <input name="category" placeholder="Kategori (mis. Pengumuman)" class="rounded-lg border border-line px-3 py-2 text-sm">
    <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
        <option value="published">Terbit</option>
        <option value="draft">Draf</option>
    </select>
    <input type="datetime-local" name="published_at" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">
    <textarea name="excerpt" rows="2" placeholder="Ringkasan singkat" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <textarea name="body" rows="5" placeholder="Isi berita" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2" required></textarea>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah berita</button>
</form>

<div class="mt-6 space-y-4">
    @forelse($items as $item)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary-dark">{{ $item->category }} · {{ $item->status === 'published' ? 'Terbit' : 'Draf' }}</p>
                    <h2 class="mt-1 text-lg font-semibold">{{ $item->title }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ optional($item->published_at)->format('d M Y H:i') ?? 'Belum dijadwalkan' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.news.destroy', $item) }}" onsubmit="return confirm('Hapus berita ini?')">
                    @csrf @method('DELETE')
                    <button class="text-sm text-red-600">Hapus</button>
                </form>
            </div>
            <form method="POST" action="{{ route('admin.news.update', $item) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf @method('PUT')
                <input name="title" value="{{ $item->title }}" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2" required>
                <input name="category" value="{{ $item->category }}" class="rounded-lg border border-line px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="published" @selected($item->status === 'published')>Terbit</option>
                    <option value="draft" @selected($item->status === 'draft')>Draf</option>
                </select>
                <input type="datetime-local" name="published_at" value="{{ optional($item->published_at)->format('Y-m-d\TH:i') }}" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">
                <textarea name="excerpt" rows="2" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2">{{ $item->excerpt }}</textarea>
                <textarea name="body" rows="4" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2" required>{{ $item->body }}</textarea>
                <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan perubahan</button>
            </form>
        </article>
    @empty
        <p class="text-sm text-muted">Belum ada berita.</p>
    @endforelse
</div>

<div class="mt-6">{{ $items->links() }}</div>
@endsection
