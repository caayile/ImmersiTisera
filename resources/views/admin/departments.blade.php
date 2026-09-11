@extends('layouts.app')
@section('title', 'Unit Bisnis')
@section('content')
<h1 class="text-2xl font-semibold">Unit Bisnis</h1>
<p class="mt-1 text-sm text-muted">Kelola unit bisnis mitra imersi.</p>
<form method="POST" enctype="multipart/form-data" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-2">
    @csrf
    <input name="name" placeholder="Nama" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input name="subtitle" placeholder="Subtitle (nama lengkap, mis. Tiga Serangkai Pustaka Mandiri)" class="rounded-lg border border-line px-3 py-2 text-sm">
    <input name="area" placeholder="Area" class="rounded-lg border border-line px-3 py-2 text-sm">
    <input name="map_url" placeholder="Link Google Maps / peta (opsional)" class="rounded-lg border border-line px-3 py-2 text-sm">
    <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
    <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah</button>
</form>
<div class="mt-6 grid gap-4 md:grid-cols-2">
    @foreach($departments as $department)
        <article class="rounded-2xl border border-line bg-white p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold">{{ $department->name }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ $department->description }}</p>
                    <p class="mt-2 text-sm">{{ $department->business_units_count }} departemen · {{ $department->status }}</p>
                </div>
                @if($department->imageUrl())
                    <img src="{{ $department->imageUrl() }}" alt="{{ $department->name }}" class="h-20 w-28 shrink-0 rounded-lg object-cover">
                @endif
            </div>
            <form method="POST" action="{{ route('admin.departments.update', $department) }}" enctype="multipart/form-data" class="mt-4 grid gap-2">
                @csrf @method('PUT')
                <input name="name" value="{{ $department->name }}" class="rounded-lg border border-line px-3 py-2 text-sm" required>
                <input name="subtitle" value="{{ $department->subtitle }}" placeholder="Subtitle (nama lengkap)" class="rounded-lg border border-line px-3 py-2 text-sm">
                <input name="area" value="{{ $department->area }}" placeholder="Area" class="rounded-lg border border-line px-3 py-2 text-sm">
                <input name="map_url" value="{{ $department->map_url }}" placeholder="Link Google Maps / peta (opsional)" class="rounded-lg border border-line px-3 py-2 text-sm">
                <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm">{{ $department->description }}</textarea>
                <input type="file" name="image" accept="image/*" class="rounded-lg border border-line px-3 py-2 text-sm">
                <div class="flex items-center gap-2">
                    <select name="status" class="flex-1 rounded-lg border border-line px-3 py-2 text-sm">
                        <option value="active" @selected($department->status === 'active')>Aktif</option>
                        <option value="disabled" @selected($department->status === 'disabled')>Nonaktif</option>
                    </select>
                    <button class="text-sm font-semibold text-primary-dark">Simpan unit bisnis</button>
                    <button type="button" class="text-sm font-semibold text-red-600" onclick="if (confirm('Hapus unit bisnis ini?')) this.closest('article').querySelector('form.delete-form').submit()">Hapus</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="delete-form hidden">
                @csrf @method('DELETE')
            </form>
        </article>
    @endforeach
</div>
@endsection