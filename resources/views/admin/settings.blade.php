@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<h1 class="text-2xl font-semibold">System Settings</h1>
<article class="mt-6 rounded-2xl border border-line bg-white p-6 text-sm">
    <p>Imersi menggunakan session authentication, role middleware, CSRF, hashed password, dan validasi upload (tipe + ukuran).</p>
    <p class="mt-3 text-muted">Program ACTIVE hanya setelah agreement AGREED. Completed hanya jika main output, final report, dan evaluasi selesai.</p>
</article>
@endsection
