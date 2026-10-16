@extends('layouts.app')
@section('title', 'Mentor')
@section('content')
<div class="overflow-x-auto rounded-2xl border border-line bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-bg text-xs uppercase text-muted"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Unit Bisnis</th><th class="px-4 py-3">Departemen</th><th class="px-4 py-3">Posisi</th><th class="px-4 py-3">Tanda tangan sertifikat</th></tr></thead>
        <tbody>
        @foreach($mentors as $mentor)
            <tr class="border-t border-line">
                <td class="px-4 py-3">{{ $mentor->user->name }}</td>
                <td class="px-4 py-3">{{ $mentor->department?->name }}</td>
                <td class="px-4 py-3">{{ $mentor->businessUnit?->name }}</td>
                <td class="px-4 py-3">{{ $mentor->position }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.mentors.certificate-signature', $mentor) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <input type="file" name="certificate_signature_file" accept="image/png,image/jpeg,image/webp" required class="max-w-56 text-xs">
                        <button class="rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white">Unggah</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
