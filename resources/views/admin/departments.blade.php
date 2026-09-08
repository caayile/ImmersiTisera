@extends('layouts.app')
@section('title', 'Unit Bisnis')
@section('content')
<h1 class="text-2xl font-semibold">Unit Bisnis</h1>
<form method="POST" class="mt-6 grid gap-3 rounded-2xl border border-line bg-white p-5 md:grid-cols-2">
    @csrf
    <input name="name" placeholder="Nama" class="rounded-lg border border-line px-3 py-2 text-sm" required>
    <input name="function" placeholder="Function" class="rounded-lg border border-line px-3 py-2 text-sm">
    <input name="area" placeholder="Area" class="rounded-lg border border-line px-3 py-2 text-sm">
    <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm md:col-span-2"></textarea>
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Tambah</button>
</form>
<div class="mt-6 grid gap-4 md:grid-cols-2">
    @foreach($departments as $department)
        <article class="rounded-2xl border border-line bg-white p-5">
            <h2 class="font-semibold">{{ $department->name }}</h2>
            <p class="mt-2 text-sm text-muted">{{ $department->description }}</p>
            <p class="mt-2 text-sm">{{ $department->business_units_count }} departemen · {{ $department->status }}</p>
            <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="mt-4 grid gap-2">
                @csrf @method('PUT')
                <input name="name" value="{{ $department->name }}" class="rounded-lg border border-line px-3 py-2 text-sm" required>
                <input name="function" value="{{ $department->function }}" placeholder="Fungsi" class="rounded-lg border border-line px-3 py-2 text-sm">
                <input name="area" value="{{ $department->area }}" placeholder="Area" class="rounded-lg border border-line px-3 py-2 text-sm">
                <textarea name="description" placeholder="Deskripsi" class="rounded-lg border border-line px-3 py-2 text-sm">{{ $department->description }}</textarea>
                <select name="status" class="rounded-lg border border-line px-3 py-2 text-sm">
                    <option value="active" @selected($department->status === 'active')>Aktif</option>
                    <option value="disabled" @selected($department->status === 'disabled')>Nonaktif</option>
                </select>
                <button class="text-left text-sm font-semibold text-primary-dark">Simpan perubahan</button>
            </form>
        </article>
    @endforeach
</div>
@endsection
