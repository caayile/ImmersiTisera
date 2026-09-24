<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Perjanjian Magang Dosen - {{ $program->participant->user->name }}</title>
    <style>
        :root { color: #18231e; font-family: Georgia, 'Times New Roman', serif; }
        body { margin: 0; background: #eef3f0; }
        .toolbar { display: flex; justify-content: space-between; gap: 1rem; max-width: 850px; margin: 1.5rem auto; font-family: Arial, sans-serif; }
        .toolbar a, .toolbar button { border: 0; border-radius: 7px; padding: .7rem 1rem; background: #1f6b4d; color: white; cursor: pointer; font-size: .9rem; text-decoration: none; }
        .toolbar a { background: transparent; color: #1f6b4d; }
        .paper { max-width: 850px; margin: 0 auto 2rem; padding: 3.2rem 4rem; background: white; box-sizing: border-box; box-shadow: 0 8px 30px rgba(18, 38, 29, .12); }
        .brand { border-bottom: 2px solid #1f6b4d; padding-bottom: 1rem; text-align: center; }
        .brand h1 { margin: 0; font-size: 1.2rem; letter-spacing: .08em; }
        .brand p { margin: .3rem 0 0; color: #5c6b63; font: .75rem Arial, sans-serif; letter-spacing: .12em; text-transform: uppercase; }
        h2 { margin: 2rem 0 1.5rem; text-align: center; font-size: 1.2rem; text-decoration: underline; }
        p, li { line-height: 1.65; font-size: .95rem; }
        .intro { margin-bottom: 1.4rem; }
        .meta { display: grid; grid-template-columns: 150px 1fr; gap: .55rem 1rem; margin: 1rem 0 1.5rem; }
        .meta dt { font-weight: bold; }
        .meta dd { margin: 0; padding-left: .25rem; }
        .section-title { margin: 1.4rem 0 .45rem; font-weight: bold; }
        .signature-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-top: 3rem; page-break-inside: avoid; }
        .signature-box { min-height: 220px; text-align: center; }
        .signature-box p { margin: .2rem 0; }
        .signature-box img { display: block; height: 125px; width: 100%; object-fit: contain; margin: .7rem auto .4rem; }
        .signature-placeholder { display: flex; height: 125px; align-items: center; justify-content: center; margin: .7rem auto .4rem; border-bottom: 1px solid #9aa9a1; color: #7b8982; font: .75rem Arial, sans-serif; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .muted { color: #5c6b63; font: .8rem Arial, sans-serif; }
        @media (max-width: 640px) {
            .toolbar { margin: 1rem; }
            .paper { margin: 0; padding: 2rem 1.3rem; }
            .meta { grid-template-columns: 1fr; gap: .15rem; }
            .signature-grid { gap: 1rem; }
            p, li { font-size: .88rem; }
        }
        @media print {
            body { background: white; }
            .toolbar { display: none; }
            .paper { max-width: none; margin: 0; padding: 1.5cm 1.7cm; box-shadow: none; }
            @page { size: A4; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ auth()->user()->isMentor() ? route('mentor.agreements') : route('participant.agreement') }}">Kembali ke perjanjian</a>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <main class="paper">
        <header class="brand">
            <h1>PROGRAM MAGANG DOSEN TSU</h1>
            <p>Surat Perjanjian Pelaksanaan Program</p>
        </header>

        <h2>PERJANJIAN MAGANG DOSEN</h2>
        <p class="intro">Pada hari ini, para pihak yang bertanda tangan di bawah ini sepakat untuk melaksanakan Program Magang Dosen dengan ketentuan dan data sebagai berikut:</p>

        <dl class="meta">
            <dt>Pihak Pertama</dt>
            <dd>{{ $program->participant->user->name }}</dd>
            <dt>NIDN</dt>
            <dd>{{ $program->participant->nidn ?: '-' }}</dd>
            <dt>Fakultas</dt>
            <dd>{{ $program->participant->faculty ?: '-' }}</dd>
            <dt>Program Studi</dt>
            <dd>{{ $program->participant->study_program ?: '-' }}</dd>
            <dt>Pihak Kedua</dt>
            <dd>{{ $program->mentor->user->name }}</dd>
            <dt>Jabatan</dt>
            <dd>{{ $program->mentor->position ?: 'Mentor Industri' }}</dd>
            <dt>Unit Bisnis</dt>
            <dd>{{ $program->businessUnit->name }}, {{ $program->department->name }}</dd>
            <dt>Periode</dt>
            <dd>{{ $program->start_date?->format('d F Y') ?: '-' }} sampai {{ $program->end_date?->format('d F Y') ?: '-' }}</dd>
        </dl>

        <p class="section-title">1. Tujuan bersama</p>
        <p>{{ $agreement->objective }}</p>
        <p class="section-title">2. Aktivitas dan ruang lingkup</p>
        <p>{{ $agreement->activities }}</p>
        <p class="section-title">3. Permasalahan atau peluang</p>
        <p>{{ $agreement->problem_statement }}</p>
        <p class="section-title">4. Luaran utama</p>
        <p>{{ $agreement->main_output }}</p>
        <p class="section-title">5. Manfaat</p>
        <p><strong>Bagi dosen:</strong> {{ $agreement->participant_benefit }}</p>
        <p><strong>Bagi unit bisnis:</strong> {{ $agreement->business_benefit }}</p>
        <p class="section-title">6. Indikator keberhasilan</p>
        <ol>
            @foreach($agreement->success_indicators ?? [] as $indicator)
                @if($indicator)<li>{{ $indicator }}</li>@endif
            @endforeach
        </ol>
        @if($agreement->collaboration_potential)
            <p class="section-title">7. Potensi kolaborasi lanjutan</p>
            <p>{{ $agreement->collaboration_potential }}</p>
        @endif

        <div class="signature-grid">
            <div class="signature-box">
                <p>Pihak Pertama,</p>
                @if($agreement->participant_signature)
                    <img src="{{ $agreement->participant_signature }}" alt="Tanda tangan {{ $program->participant->user->name }}">
                @else
                    <div class="signature-placeholder">Belum ditandatangani</div>
                @endif
                <p class="signature-name">{{ $program->participant->user->name }}</p>
                <p class="muted">Disetujui {{ $agreement->participant_approved_at?->format('d F Y') }}</p>
            </div>
            <div class="signature-box">
                <p>Pihak Kedua,</p>
                @if($agreement->mentor_signature)
                    <img src="{{ $agreement->mentor_signature }}" alt="Tanda tangan {{ $program->mentor->user->name }}">
                @else
                    <div class="signature-placeholder">Belum ditandatangani</div>
                @endif
                <p class="signature-name">{{ $program->mentor->user->name }}</p>
                <p class="muted">Disetujui {{ $agreement->mentor_approved_at?->format('d F Y') }}</p>
            </div>
        </div>
    </main>
</body>
</html>
