<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat {{ $certificate->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, 'Times New Roman', serif; color: #0f2a24; background: #f6fbf8; }
        .toolbar { display: flex; justify-content: flex-end; gap: .5rem; max-width: 980px; margin: 1rem auto; font-family: Arial, sans-serif; }
        .toolbar a, .toolbar button { border: 0; border-radius: 8px; padding: .7rem 1rem; background: #1f6b4d; color: #fff; text-decoration: none; cursor: pointer; }
        .sheet {
            max-width: 980px;
            margin: 0 auto 2rem;
            min-height: 680px;
            padding: 3rem 3.5rem;
            border: 10px solid #5ec69d;
            background:
                linear-gradient(180deg, rgba(255,255,255,.96), rgba(246,251,248,.98)),
                radial-gradient(circle at top right, rgba(94,198,157,.18), transparent 40%);
            box-shadow: 0 16px 40px rgba(15,42,36,.08);
        }
        .eyebrow { letter-spacing: .22em; text-transform: uppercase; font-size: 11px; color: #3eaa84; margin: 0; }
        h1 { margin: .6rem 0 0; font-size: 34px; }
        .meta { margin-top: 1.5rem; font-size: 15px; line-height: 1.7; }
        .name { margin: 1.8rem 0 .4rem; font-size: 36px; font-weight: 700; }
        .detail { margin: 0; font-size: 16px; line-height: 1.7; }
        .footer { display: flex; justify-content: space-between; gap: 2rem; margin-top: 3.5rem; }
        .sign { width: 45%; text-align: center; }
        .sign img { display: block; height: 70px; margin: 0 auto 8px; object-fit: contain; }
        .sign .who { font-weight: 700; text-decoration: underline; }
        .sign .role { margin-top: 2px; font-size: 13px; color: #5b6b66; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; margin: 0; max-width: none; }
        }
    </style>
</head>
<body>
@php
    $program = $certificate->program;
    $participant = $program->participant;
    $mentor = $program->mentor;
@endphp
<div class="toolbar">
    <a href="{{ url()->previous() }}">Kembali</a>
    <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>
<main class="sheet">
    <p class="eyebrow">Program Magang Dosen TSU</p>
    <h1>Sertifikat Penyelesaian</h1>
    <p class="meta">Nomor {{ $certificate->number }}</p>
    <p class="meta">Dengan ini menyatakan bahwa</p>
    <p class="name">{{ $participant->user->name }}</p>
    <p class="detail">
        telah menyelesaikan Program Magang Dosen pada
        <strong>{{ $program->businessUnit?->name ?? '-' }}</strong>
        · <strong>{{ $program->department?->name ?? '-' }}</strong>
        @if($program->start_date && $program->end_date)
            selama periode {{ $program->start_date->format('d M Y') }} s.d. {{ $program->end_date->format('d M Y') }}.
        @else
            .
        @endif
    </p>
    <p class="detail" style="margin-top:1rem">
        Sertifikat ini diterbitkan secara digital pada {{ $certificate->issued_at?->format('d F Y') ?? now()->format('d F Y') }}.
    </p>
    <div class="footer">
        <div class="sign">
            <p>Peserta,</p>
            <div style="height:70px"></div>
            <p class="who">{{ $participant->user->name }}</p>
            <p class="role">Dosen Peserta</p>
        </div>
        <div class="sign">
            <p>Mentor,</p>
            @if($certificate->mentor_signature)
                <img src="{{ $certificate->mentor_signature }}" alt="Tanda tangan mentor">
            @else
                <div style="height:70px"></div>
            @endif
            <p class="who">{{ $mentor?->user?->name ?? 'Mentor' }}</p>
            <p class="role">Mentor Industri</p>
        </div>
    </div>
</main>
</body>
</html>
