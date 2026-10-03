<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Nilai Magang Dosen - {{ $program->participant->user->name ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #ffffff; color: #000000; font-family: Georgia, 'Times New Roman', serif; }
        .toolbar { display: flex; justify-content: space-between; gap: .75rem; max-width: 850px; margin: 1.5rem auto; font-family: Arial, sans-serif; }
        .toolbar .left { display: flex; gap: .5rem; }
        .toolbar a, .toolbar button { border: 0; border-radius: 7px; padding: .7rem 1rem; background: #1f6b4d; color: #fff; cursor: pointer; font-size: .9rem; text-decoration: none; display: inline-block; }
        .toolbar a.back { background: #fff; color: #1f6b4d; border: 1px solid #1f6b4d; }
        .paper { max-width: 850px; margin: 0 auto 2rem; padding: 3rem 3.5rem; background: #ffffff; }
        .kop { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 18px; }
        .kop table { width: 100%; border-collapse: collapse; }
        .kop td { vertical-align: middle; padding: 0; }
        .kop h1 { margin: 0; font-size: 16pt; letter-spacing: .04em; text-align: center; }
        .kop p { margin: 4px 0 0; font-size: 11pt; text-align: center; }
        .kop img.logo { display: block; width: 72px; }
        p, li, td, th { font-size: 11pt; line-height: 1.65; color: #000; }
        .info { width: 100%; border-collapse: collapse; margin: 8px 0 16px; }
        .info > tbody > tr > td { vertical-align: top; padding: 0; }
        .info table { border-collapse: collapse; }
        .info table td { vertical-align: top; padding: 2px 4px; text-align: left; }
        .info .kanan table { margin-left: auto; }
        table.nilai { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
        table.nilai th, table.nilai td { border: 1px solid #000; padding: 5px 8px; text-align: left; font-size: 10pt; line-height: 1.5; }
        table.nilai thead th { font-size: 9pt; }
        table.nilai td.aspek { font-size: 11pt; }
        table.nilai th { background: #e8e8e8; text-align: center; font-weight: bold; }
        table.nilai td.no, table.nilai td.angka { text-align: center; }
        table.nilai tr.grup td { font-weight: bold; }
        table.nilai tr.total td { font-weight: bold; background: #e8e8e8; }
        table.nilai th, table.nilai tr.total td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .legenda { margin-top: 14px; }
        .legenda p { margin: 2px 0; }
        .ttd { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 16px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 12px; }
        .ttd td p { text-align: center; margin: 2px 0; }
        .ttd .nama { font-weight: bold; text-decoration: underline; margin: 2px 0; text-align: center; }
        .ttd .spasi { height: 64px; }
        .ttd .nomor { margin: 2px 0; text-align: center; }
        @page { size: A4; margin: 1.5cm 2.5cm 2cm; }
        @media print {
            .toolbar { display: none !important; }
            .paper { max-width: none; margin: 0; padding: 0; }
            .kop { margin-top: 0; }
        }
        @if(($pdf ?? false))
        .toolbar { display: none !important; }
        .paper { max-width: none; margin: 0; padding: 0; }
        @endif
    </style>
</head>
<body>
@if(! ($pdf ?? false))
<div class="toolbar">
    <div class="left">
        <a class="back" href="{{ route('participant.evaluation') }}">← Kembali</a>
    </div>
    <div class="left">
        <button type="button" onclick="window.print()">Cetak</button>
    </div>
</div>
@endif
@php
    $dosen = $program->participant;
    $dosenUser = $dosen->user ?? null;
    $mentorUser = $program->mentor?->user;
@endphp
@php
    $logoSrc = ($pdf ?? false) ? public_path('images/logo-tsu.png') : asset('images/logo-tsu.png');
@endphp
<div class="paper">
    <div class="kop">
        <table>
            <tr>
                <td style="width: 80px;"><img class="logo" src="{{ $logoSrc }}" alt="TS"></td>
                <td>
                    <h1>LAPORAN NILAI MAGANG DOSEN</h1>
                    <p>Program Magang Dosen TSU</p>
                </td>
                <td style="width: 80px;"></td>
            </tr>
        </table>
    </div>

    <table class="info">
        <tr>
            <td class="kiri">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>:</td>
                        <td>{{ $dosenUser?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>NIDN/NIK</td>
                        <td>:</td>
                        <td>{{ $dosen?->nidn ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Fakultas/Progdi</td>
                        <td>:</td>
                        <td>{{ trim(($dosen?->faculty ?? '').' / '.($dosen?->study_program ?? ''), ' /') ?: '—' }}</td>
                    </tr>
                </table>
            </td>
            <td class="kanan">
                <table>
                    <tr>
                        <td>Mentor</td>
                        <td>:</td>
                        <td>{{ $mentorUser?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Unit Bisnis</td>
                        <td>:</td>
                        <td>{{ $program->department?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Departemen</td>
                        <td>:</td>
                        <td>{{ $program->businessUnit?->name ?? '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p><b>Daftar Nilai</b></p>
    <table class="nilai">
        <thead>
            <tr>
                <th style="width: 8%;">NO</th>
                <th>KOMPETENSI</th>
                <th style="width: 16%;">NILAI DALAM ANGKA</th>
                <th style="width: 16%;">NILAI DALAM HURUF</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->grade_groups as $group)
                <tr class="grup">
                    <td colspan="4">{{ $group['name'] }} (bobot {{ $group['weight'] }}%)</td>
                </tr>
                @foreach($group['aspects'] ?? [] as $aspectIndex => $aspect)
                    <tr>
                        <td class="no">{{ $aspectIndex + 1 }}</td>
                        <td class="aspek">{{ $aspect['label'] }}</td>
                        <td class="angka">{{ $aspect['score'] }}</td>
                        <td class="angka">{{ \App\Models\Evaluation::predicateFor(isset($aspect['score']) ? (float) $aspect['score'] : null) ?? '—' }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="total">
                <td colspan="2">Nilai Akhir</td>
                <td class="angka">{{ $report->reportAverage() }}</td>
                <td class="angka">{{ $report->predicate() }}</td>
            </tr>
        </tbody>
    </table>

    @if(filled($report->comments))
        <p><b>Catatan mentor:</b> {{ $report->comments }}</p>
    @endif

    <div class="legenda">
        <p><b>Konversi predikat:</b></p>
        <p>A : 85 - 100&nbsp;&nbsp;|&nbsp;&nbsp;B : 70 - 84&nbsp;&nbsp;|&nbsp;&nbsp;C : 60 - 74&nbsp;&nbsp;|&nbsp;&nbsp;D : 50 - 59&nbsp;&nbsp;|&nbsp;&nbsp;E : 0 - 49</p>
    </div>

    <table class="ttd">
        <tr>
            <td>
                <p>Mentor,</p>
                <div class="spasi"></div>
                <p class="nama">{{ $mentorUser?->name ?? '—' }}</p>
                <p class="nomor">NIK: {{ $program->mentor?->nik ?? '—' }}</p>
            </td>
            <td>
                <p>Peserta,</p>
                <div class="spasi"></div>
                <p class="nama">{{ $dosenUser?->name ?? '—' }}</p>
                <p class="nomor">NIDN/NIK: {{ $dosen?->nidn ?? '—' }}</p>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
