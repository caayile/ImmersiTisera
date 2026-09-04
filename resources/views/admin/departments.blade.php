@extends('layouts.app')
@section('title', 'Department')
@section('content')
<h1 class="text-2xl font-semibold">Department</h1>
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
            <p class="mt-2 text-sm">{{ $department->business_units_count }} unit · {{ $department->status }}</p>
            <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="mt-3">
                @csrf @method('PUT')
                <input type="hidden" name="name" value="{{ $department->name }}">
                <input type="hidden" name="description" value="{{ $department->description }}">
                <input type="hidden" name="function" value="{{ $department->function }}">
                <input type="hidden" name="status" value="{{ $department->status === 'active' ? 'disabled' : 'active' }}">
                <button class="text-sm text-primary-dark">{{ $department->status === 'active' ? 'Disable' : 'Enable' }}</button>
            </form>
        </article>
    @endforeach
</div>
@endsection
