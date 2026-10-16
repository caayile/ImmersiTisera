@extends('layouts.app')
@section('title', 'Pengaturan Sistem')
@section('content')
<article class="rounded-2xl border border-line bg-white p-6 text-sm">
    <p>Magang Dosen menggunakan autentikasi sesi, middleware peran, perlindungan CSRF, kata sandi terenkripsi, serta validasi unggahan (jenis dan ukuran berkas).</p>
    <p class="mt-3 text-muted">Program dimulai setelah perjanjian disepakati. Program selesai jika hasil utama, laporan akhir, dan evaluasi sudah lengkap.</p>
</article>
<article class="mt-5 rounded-2xl border border-line bg-white p-6">
    <h2 class="text-lg font-semibold">Pejabat penandatangan sertifikat</h2>
    <form method="POST" action="{{ route('admin.settings.certificate-signatory') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 sm:grid-cols-2">
        @csrf
        @method('PUT')
        <label class="grid gap-1 text-sm font-medium">Nama
            <input name="name" value="{{ old('name', $certificateSignatory?->name) }}" required maxlength="160" class="rounded-lg border border-line px-3 py-2 font-normal">
        </label>
        <label class="grid gap-1 text-sm font-medium">Jabatan
            <input name="title" value="{{ old('title', $certificateSignatory?->title) }}" required maxlength="160" class="rounded-lg border border-line px-3 py-2 font-normal">
        </label>
        <label class="grid gap-1 text-sm font-medium sm:col-span-2">Organisasi
            <input name="organization" value="{{ old('organization', $certificateSignatory?->organization) }}" required maxlength="200" class="rounded-lg border border-line px-3 py-2 font-normal">
        </label>
        <label class="grid gap-1 text-sm font-medium sm:col-span-2">Tanda tangan (PNG, JPG, atau WebP, maksimal 1 MB)
            <input type="file" name="signature_file" accept="image/png,image/jpeg,image/webp" class="text-sm font-normal">
        </label>
        <div class="sm:col-span-2">
            <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Simpan pejabat</button>
        </div>
    </form>
</article>
@endsection
