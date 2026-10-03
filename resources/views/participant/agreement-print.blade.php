<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perjanjian Magang Dosen - {{ $agreement->letter_number ?? 'Menunggu nomor' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #ffffff; color: #000000; font-family: Georgia, 'Times New Roman', serif; }
        .toolbar { display: flex; justify-content: space-between; gap: .75rem; max-width: 850px; margin: 1.5rem auto; font-family: Arial, sans-serif; }
        .toolbar .left { display: flex; gap: .5rem; }
        .toolbar a, .toolbar button { border: 0; border-radius: 7px; padding: .7rem 1rem; background: #1f6b4d; color: #fff; cursor: pointer; font-size: .9rem; text-decoration: none; display: inline-block; }
        .toolbar a.back { background: transparent; color: #1f6b4d; }
        .toolbar a.download { background: #0f2a24; }
        .paper { max-width: 850px; margin: 0 auto 2rem; padding: 3rem 3.5rem; background: #ffffff; }
        .kop { border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 18px; }
        .kop table { width: 100%; border-collapse: collapse; }
        .kop td { vertical-align: middle; padding: 0; }
        .kop h1 { margin: 0; font-size: 16pt; letter-spacing: .04em; text-align: center; }
        .kop p { margin: 4px 0 0; font-style: italic; font-size: 11pt; text-align: center; }
        .kop img.logo { display: block; width: 72px; }
        .judul { margin: 18px 0 14px; }
        .judul h2 { margin: 0; font-size: 12pt; text-align: center; font-weight: normal; }
        .judul p { margin: 4px 0 0; font-size: 13pt; text-align: center; }
        p, li, td, th { font-size: 12pt; line-height: 1.65; color: #000; }
        p.isi { margin: 0 0 10px; text-align: justify; }
        .identitas { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
        .identitas td { vertical-align: top; padding: 2px 4px; text-align: left; }
        .identitas td.lbl { font-weight: bold; white-space: nowrap; }
        .ttd { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 16px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 12px; }
        .ttd td p { text-align: center; margin: 2px 0; }
        .ttd img { display: block; height: 80px; width: 100%; object-fit: contain; margin: 6px auto 4px; }
        .ttd .nama { font-weight: bold; text-decoration: underline; margin: 2px 0; text-align: center; }
        .ttd .nomor { margin: 2px 0; text-align: center; }
        .kota-tanggal { text-align: right; margin: 18px 0 0; }
        @media (max-width: 640px) { .toolbar { margin: 1rem; } .paper { margin: 0; padding: 2rem 1.3rem; } }
        @page { size: A4; margin: 1.6cm 2cm 2cm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .paper { max-width: none; margin: 0; padding: 0; }
            .kop { position: fixed; top: 0; left: 0; right: 0; padding: 0 2cm; background: #fff; }
            .kop img.logo { width: 40px; }
            .kop h1 { font-size: 13pt; }
            .kop p { font-size: 9pt; }
            .kop { padding-bottom: 8px; margin-bottom: 0; }
            * { color: #000 !important; box-shadow: none !important; text-shadow: none !important; }
            a { text-decoration: none !important; }
        }
        @if(($pdf ?? false))
        .toolbar { display: none !important; }
        .paper { max-width: none; margin: 0; padding: 0; }
        .kop { position: fixed; top: 0; left: 0; right: 0; padding: 0 2cm; background: #fff; }
        .kop img.logo { width: 40px; }
        .kop h1 { font-size: 13pt; }
        .kop p { font-size: 9pt; }
        .kop { padding-bottom: 8px; margin-bottom: 0; }
        @endif
    </style>
</head>
<body>
@php
    $isPdf = $pdf ?? false;
    $bulanId = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $namaBulan = fn ($date) => $bulanId[(int) $date->format('n')] ?? $date->format('F');
    $tglSurat = fn ($date) => $date->format('d').' '.$namaBulan($date).' '.$date->format('Y');
    $issued = $agreement->letter_issued_at ?? $agreement->mentor_approved_at ?? now();
    $dosen = $program->participant;
    $dosenUser = $dosen->user ?? null;
    $mentor = $program->mentor;
    $mentorUser = $mentor->user ?? null;
    // Periode memakai tanggal usulan pendaftar di form pendaftaran,
    // jatuh kembali ke tanggal program bila tidak tersedia.
    $periodeMulai = $program->application?->period_start ?? $program->start_date;
    $periodeSelesai = $program->application?->period_end ?? $program->end_date;
    $minggu = ($periodeMulai && $periodeSelesai) ? max(1, (int) floor($periodeMulai->diffInDays($periodeSelesai) / 7)) : 8;
    $periode = $periodeMulai && $periodeSelesai
        ? $tglSurat($periodeMulai).' s.d. '.$tglSurat($periodeSelesai).' (± '.$minggu.' minggu)'
        : '—';
    $indicators = collect($agreement->success_indicators ?? [])->filter()->values();
    $indikatorTeks = $indicators->isNotEmpty()
        ? $indicators->map(fn ($ind, $i) => ($i + 1).') '.trim((string) $ind))->implode(' ')
        : '—';
    $backUrl = auth()->check() && auth()->user()->isMentor() ? route('mentor.agreements') : route('participant.agreement');
    $downloadUrl = isset($agreement->id) && auth()->check()
        ? (auth()->user()->isMentor()
            ? route('mentor.agreements.download', $agreement)
            : (auth()->user()->isAdmin() ? route('admin.agreements.download', $agreement) : route('participant.agreement.download')))
        : '#';
@endphp
    @unless($isPdf)
    <div class="toolbar">
        <div class="left">
            <a class="back" href="{{ $backUrl }}">Kembali ke perjanjian</a>
        </div>
        <div class="left">
            <a class="download" href="{{ $downloadUrl }}">Unduh PDF</a>
            <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        </div>
    </div>
    @endunless

@php
    $logoSrc = ($pdf ?? false) ? public_path('images/logo-tsu.png') : asset('images/logo-tsu.png');
@endphp
    <main class="paper">
        <div class="kop">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 80px; vertical-align: middle; padding: 0;"><img class="logo" src="{{ $logoSrc }}" alt="TS"></td>
                    <td style="vertical-align: middle; padding: 0;">
                        <h1>PROGRAM MAGANG DOSEN TSU</h1>
                        <p>Surat Perjanjian Pelaksanaan Program</p>
                    </td>
                    <td style="width: 80px; padding: 0;"></td>
                </tr>
            </table>
        </div>

        <div class="judul">
            <h2>No. {{ $agreement->letter_number ?? '____/MD/TSU/TS/__-____' }}</h2>
            <p>Perjanjian Magang Dosen</p>
        </div>

        <p class="isi">Kepada Yth.<br><strong>{{ $dosenUser->name ?? '-' }}</strong><br>di tempat</p>

        <p class="isi">Dengan hormat pada tanggal <strong>{{ $issued->format('d') }}</strong> bulan <strong>{{ $namaBulan($issued) }}</strong> tahun <strong>{{ $issued->format('Y') }}</strong>, sehubungan dengan telah disetujuinya pendaftaran Anda pada Program Magang Dosen TSU, dengan ini kami sampaikan Perjanjian Magang Dosen dengan rincian sebagai berikut:</p>

        <table class="identitas">
            <tr><td class="lbl" style="width:220px">Dosen</td><td style="width:12px">:</td><td>{{ $dosenUser->name ?? '-' }}</td></tr>
            <tr><td class="lbl">NIDN</td><td>:</td><td>{{ $dosen->nidn ?? '-' }}</td></tr>
            <tr><td class="lbl">Fakultas / Program Studi</td><td>:</td><td>{{ trim(($dosen->faculty ?? '').' / '.($dosen->study_program ?? ''), ' /') ?: '-' }}</td></tr>
            <tr><td class="lbl">Mentor</td><td>:</td><td>{{ $mentorUser->name ?? '-' }}</td></tr>
            <tr><td class="lbl">Unit Bisnis / Departemen</td><td>:</td><td>{{ trim(($program->department?->name ?? '').' / '.($program->businessUnit?->name ?? ''), ' /') ?: '-' }}</td></tr>
            <tr><td class="lbl">Periode Program</td><td>:</td><td>{{ $periode }}</td></tr>
            <tr><td class="lbl">Indikator Keberhasilan</td><td>:</td><td>{{ $indikatorTeks }}</td></tr>
        </table>

        <p class="isi">Berdasarkan data tersebut, Dosen dan Mentor sepakat menjalankan Program Magang Dosen TSU dengan ketentuan sebagai berikut: selama periode magang aktif, Peserta Dosen wajib mengisi logbook aktivitas setiap hari, mengikuti sesi mentoring bersama Mentor setiap minggu, serta mengikuti checkpoint evaluasi bersama Mentor setiap 2 minggu sekali; pada akhir periode magang, Dosen wajib menyerahkan hasil tugas observasi atau riset kepada Mentor.</p>

        <p class="isi">Mentor berkomitmen membimbing dan memberikan evaluasi pada setiap checkpoint di atas. Dosen dan Mentor sepakat menjaga kerahasiaan data ataupun informasi selama program berlangsung, dan setiap perubahan atas Perjanjian ini hanya berlaku apabila disetujui secara tertulis/digital melalui sistem Program Magang Dosen TSU.</p>

        <p class="isi">Perjanjian ini dinyatakan berlaku dan status Program Magang dinyatakan Aktif setelah disetujui/ditandatangani secara digital oleh Dosen dan Mentor.</p>

        <p class="isi">Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>

        <p class="kota-tanggal">Surakarta, {{ ltrim($issued->format('d'), '0').' '.$namaBulan($issued).' '.$issued->format('Y') }}</p>

        <table class="ttd">
            <tr>
                <td>
                    <p>Dosen,</p>
                    @if(!empty($agreement->participant_signature))
                        <img src="{{ $agreement->participant_signature }}" alt="Tanda tangan {{ $dosenUser->name ?? '' }}">
                    @else
                        <div style="height:80px"></div>
                    @endif
                    <p class="nama">{{ $dosenUser->name ?? '........................' }}</p>
                    <p class="nomor">NIDN: {{ $dosen->nidn ?? '—' }}</p>
                </td>
                <td>
                    <p>Mentor,</p>
                    @if(!empty($agreement->mentor_signature))
                        <img src="{{ $agreement->mentor_signature }}" alt="Tanda tangan {{ $mentorUser->name ?? '' }}">
                    @else
                        <div style="height:80px"></div>
                    @endif
                    <p class="nama">{{ $mentorUser->name ?? '........................' }}</p>
                    <p class="nomor">NIK: {{ $mentor->nik ?? '—' }}</p>
                </td>
            </tr>
        </table>
    </main>
</body>
</html>
