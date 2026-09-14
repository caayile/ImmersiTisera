@extends('layouts.app')
@section('title', 'Unit Bisnis')
@section('content')
<div x-data="{ showAdd: false }">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Unit Bisnis</h1>
            <p class="mt-1 text-sm text-muted">Pilih unit bisnis untuk mengelola informasi, lokasi, dan departemen di dalamnya.</p>
        </div>
        <button type="button" @click="showAdd = !showAdd" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-dark">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah Unit Bisnis
        </button>
    </div>

    <section x-show="showAdd" x-cloak class="mt-6 rounded-2xl border border-line bg-white p-5">
        <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink">
            <span class="material-symbols-outlined text-[18px] text-primary-dark">apartment</span>
            Tambah unit bisnis baru
        </h2>
        <form method="POST" action="{{ route('admin.departments') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
            @csrf
            <input name="name" placeholder="Nama unit bisnis" class="rounded-lg border border-line px-3 py-2 text-sm" required>
            <input name="subtitle" placeholder="Subtitle (nama lengkap, mis. Tiga Serangkai Pustaka Mandiri)" class="rounded-lg border border-line px-3 py-2 text-sm">
            <input name="area" placeholder="Area / Lokasi (mis. Surakarta)" class="rounded-lg border border-line px-3 py-2 text-sm">
            <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
            <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
            <button class="justify-self-start rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah Unit Bisnis</button>
        </form>
    </section>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white">
    @forelse($departments as $department)
        <div class="flex items-center gap-4 border-b border-line px-5 py-4 last:border-0 transition hover:bg-bg/50">
            @if($department->imageUrl())
                <img src="{{ $department->imageUrl() }}" alt="{{ $department->name }}" class="h-14 w-20 shrink-0 rounded-lg object-cover">
            @else
                <span class="flex h-14 w-20 shrink-0 items-center justify-center rounded-lg bg-primary/12 text-primary-dark">
                    <span class="material-symbols-outlined text-[26px]">apartment</span>
                </span>
            @endif
            <a href="{{ route('admin.departments.show', $department) }}" class="min-w-0 flex-1">
                <h3 class="font-semibold text-ink">{{ $department->name }}</h3>
                <p class="mt-0.5 truncate text-xs text-muted">{{ $department->subtitle ?: ($department->area ?: 'Unit Bisnis Mitra') }}</p>
            </a>
            <div class="flex shrink-0 items-center gap-1.5">
                <a href="{{ route('admin.departments.show', $department) }}" title="Edit" class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-muted transition hover:border-primary hover:text-primary-dark">
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                </a>
                <button type="button" title="Hapus" onclick="if (confirm('Hapus unit bisnis ini?')) document.getElementById('delete-department-{{ $department->id }}').submit()" class="flex h-9 w-9 items-center justify-center rounded-lg border border-line text-muted transition hover:border-red-300 hover:text-red-600">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
            </div>
        </div>
    @empty
        <p class="px-5 py-8 text-sm text-muted">Belum ada unit bisnis. Klik "Tambah Unit Bisnis" untuk menambahkan.</p>
    @endforelse
</div>

@foreach($departments as $department)
    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" id="delete-department-{{ $department->id }}" class="hidden">
        @csrf @method('DELETE')
    </form>
@endforeach
@endsection