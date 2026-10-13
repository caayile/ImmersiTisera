<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perjanjian Kerja Sama - {{ $agreement->letter_number ?? 'Menunggu nomor' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #ffffff; color: #000000; font-family: Arial, Helvetica, sans-serif; }
        .toolbar { display: flex; justify-content: space-between; gap: .75rem; max-width: 210mm; margin: 1.5rem auto; font-family: Arial, sans-serif; }
        .toolbar .left { display: flex; gap: .5rem; }
        .toolbar a, .toolbar button { border: 0; border-radius: 7px; padding: .7rem 1rem; background: #1f6b4d; color: #fff; cursor: pointer; font-size: .9rem; text-decoration: none; display: inline-block; }
        .toolbar a.back { background: transparent; color: #1f6b4d; }
        .toolbar a.download { background: #0f2a24; }
        .paper {
            width: 210mm;
            min-height: 297mm;
            max-width: 100%;
            margin: 0 auto 2rem;
            padding: 2.2cm 2.5cm 2.2cm 2.5cm;
            background: #ffffff;
            box-shadow: 0 0 0 1px #e5e7eb;
        }
        .doc-title { text-align: center; margin: 0 0 4px; }
        .doc-title h1 { margin: 0; font-size: 11pt; font-weight: bold; letter-spacing: 0.02em; }
        .doc-title h2 { margin: 2px 0 0; font-size: 11pt; font-weight: bold; }
        .doc-title h3 { margin: 2px 0 0; font-size: 11pt; font-weight: bold; }
        .rule { border-bottom: 2px solid #0b2545; margin: 10px 0 14px; }
        .nomor { text-align: center; font-size: 11pt; margin: 0 0 14px; }
        p, li, td, th { font-size: 11pt; line-height: 1.45; color: #000; }
        p.isi { margin: 0 0 10px; text-align: justify; }
        p.isi.indent { text-indent: 1.25cm; }
        table { border-collapse: collapse; width: 100%; }
        .pihak td { vertical-align: top; padding: 4px 6px; text-align: justify; }
        .pihak .no { width: 28px; }
        .pihak .nama { width: 28%; }
        .pihak .sep { width: 16px; text-align: center; }
        .kesepakatan { margin: 12px 0; }
        .kesepakatan th, .kesepakatan td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; text-align: left; }
        .kesepakatan th { text-align: center; font-weight: normal; }
        .kesepakatan .col-no { width: 36px; text-align: center; }
        .kesepakatan ul { margin: 0; padding-left: 1.1rem; }
        .kesepakatan li { margin: 0 0 4px; }
        .ttd { margin-top: 8px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 10px; border: none; }
        .ttd p { text-align: center; margin: 2px 0; }
        .ttd img { display: block; height: 52px; width: 100%; max-width: 220px; object-fit: contain; margin: 8px auto 4px; }
        .ttd .sig-slot { height: 52px; }
        .ttd .nama { font-weight: bold; text-decoration: underline; }
        .kota-tanggal { text-align: left; margin: 18px 0 8px; }
        @media (max-width: 640px) {
            .toolbar { margin: 1rem; }
            .paper { width: auto; min-height: 0; margin: 0; padding: 1.5rem 1.25rem; box-shadow: none; }
        }
        @page { size: A4; margin: 2.2cm 2.5cm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .paper { width: auto; min-height: 0; max-width: none; margin: 0; padding: 0; box-shadow: none; }
            .signatures, .ttd { page-break-inside: avoid !important; break-inside: avoid !important; }
            * { color: #000 !important; box-shadow: none !important; }
        }
        @if(($pdf ?? false))
        .toolbar { display: none !important; }
        .paper { width: auto; min-height: 0; max-width: none; margin: 0; padding: 0; box-shadow: none; }
        .signatures, .ttd { page-break-inside: avoid !important; break-inside: avoid !important; }
        @endif
    </style>
</head>
<body>
@php
    $isPdf = $pdf ?? false;
    $bulanId = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $bulanSingkat = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $hariId = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $namaBulan = fn ($date) => $bulanId[(int) $date->format('n')] ?? $date->format('F');
    $namaBulanSingkat = fn ($date) => $bulanSingkat[(int) $date->format('n')] ?? $date->format('M');
    $tglSurat = fn ($date) => (int) $date->format('d').' '.$namaBulan($date).' '.$date->format('Y');
    $tglSingkat = fn ($date) => (int) $date->format('d').' '.$namaBulanSingkat($date).' '.$date->format('Y');
    $issued = $agreement->letter_issued_at ?? $agreement->mentor_approved_at ?? now();
    $hari = $hariId[$issued->format('l')] ?? $issued->format('l');
    $dosen = $program->participant;
    $dosenUser = $dosen->user ?? null;
    $mentor = $program->mentor;
    $mentorUser = $mentor->user ?? null;
    $periodeMulai = $program->application?->period_start ?? $program->start_date;
    $periodeSelesai = $program->application?->period_end ?? $program->end_date;
    $periode = $periodeMulai && $periodeSelesai
        ? $tglSingkat($periodeMulai).' – '.$tglSingkat($periodeSelesai)
        : '—';
    $deptName = trim(($program->department?->name ?? '').($program->businessUnit?->name ? ', '.$program->businessUnit?->name : ''), ', ') ?: 'Unit Bisnis Tiga Serangkai';
    $programBatch = 'Magang Dosen TSU Batch #1 '.($periodeMulai?->format('Y') ?? $issued->format('Y'));
    $letterDisplay = $agreement->letter_number ?? '____/MD.TSU/B1/X/____';
    $fakultasProdi = trim(($dosen->faculty ?? 'Fakultas Teknik').', Prodi '.($dosen->study_program ?? '—'));
    $mentorJabatan = filled($mentor?->position) ? $mentor->position : 'Supervisor '.$program->department?->name;
    $tsuAddress = 'Jl. Samanhudi No. 84-86, Purwosari, Surakarta';
    $ptAddress = 'Jl. Dr. Supomo No. 23, Sriwedari, Surakarta';
    $tujuanItems = array_values(array_filter([
        filled($agreement->objective) ? 'Mengobservasi '.$agreement->objective.' di '.$deptName : null,
        filled($agreement->main_output) ? 'Membuat rekomendasi '.$agreement->main_output.' di '.$deptName : null,
        filled($agreement->activities) ? $agreement->activities : null,
        ...array_filter((array) ($agreement->success_indicators ?? [])),
    ]));
    if ($tujuanItems === []) {
        $tujuanItems = [
            'Mengobservasi proses ……. di '.$deptName,
            'Membuat rekomendasi proses …… di '.$deptName,
            'Meningkatkan ……. sebesar ….. dengan cara ……',
        ];
    }
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

    <main class="paper">
        <div class="doc-title">
            <h1>PERJANJIAN KERJA SAMA</h1>
            <h2>PROGRAM MAGANG DOSEN TIGA SERANGKAI UNIVERSITY</h2>
            <h3>DI UNIT BISNIS TIGA SERANGKAI</h3>
        </div>
        <div class="rule"></div>

        <p class="nomor">No. {{ $letterDisplay }}</p>

        <p class="isi">Pada hari ini, {{ $hari }} tanggal <strong>{{ $issued->format('d') }}</strong> bulan <strong>{{ $namaBulan($issued) }}</strong> tahun <strong>{{ $issued->format('Y') }}</strong>, bertempat di Surakarta, kami yang bertanda tangan di bawah ini:</p>

        <table class="pihak">
            <tr>
                <td class="no">1.</td>
                <td class="nama">{{ $dosenUser->name ?? '—' }}</td>
                <td class="sep">:</td>
                <td>
                    Dosen {{ $fakultasProdi }}, NIDN {{ $dosen->nidn ?? '—' }}, berkedudukan di {{ $tsuAddress }}, bertindak untuk dan atas nama Tiga Serangkai University, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>;
                </td>
            </tr>
            <tr>
                <td class="no">2.</td>
                <td class="nama">{{ $mentorUser->name ?? '—' }}</td>
                <td class="sep">:</td>
                <td>
                    {{ $mentorJabatan }}, berkedudukan di {{ $ptAddress }}, bertindak untuk dan atas nama PT Tiga Serangkai, selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong>;
                </td>
            </tr>
        </table>

        <p class="isi">PIHAK PERTAMA dan PIHAK KEDUA untuk selanjutnya disebut sebagai PARA PIHAK. PARA PIHAK sepakat untuk melaksanakan kerja sama untuk mendukung kolaborasi industri melalui Program Magang Dosen TSU di Unit Bisnis Tiga Serangkai dengan ketentuan sebagai berikut:</p>

        <table class="kesepakatan">
            <thead>
                <tr>
                    <th class="col-no">No.</th>
                    <th style="width:26%">Poin Kesepakatan</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-no">1.</td>
                    <td>Nama Program</td>
                    <td>{{ $programBatch }}</td>
                </tr>
                <tr>
                    <td class="col-no">2.</td>
                    <td>Waktu</td>
                    <td>{{ $periode }}</td>
                </tr>
                <tr>
                    <td class="col-no">3.</td>
                    <td>Tempat</td>
                    <td>{{ $deptName }}</td>
                </tr>
                <tr>
                    <td class="col-no">4.</td>
                    <td>Partisipan</td>
                    <td>
                        <ul>
                            <li>{{ $mentorUser->name ?? '—' }} sebagai Mentor</li>
                            <li>{{ $dosenUser->name ?? '—' }} sebagai Peserta</li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <td class="col-no">6.</td>
                    <td>Tujuan</td>
                    <td>
                        <ul>
                            @foreach($tujuanItems as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="isi indent">PARA PIHAK sepakat menjaga kerahasiaan data dan informasi selama program berlangsung, dan setiap perubahan atas perjanjian ini hanya berlaku apabila disetujui secara tertulis/digital melalui sistem Program Magang Dosen TSU. Perjanjian ini dinyatakan berlaku dan status Program Magang dinyatakan Aktif setelah disetujui/ditandatangani secara digital oleh PARA PIHAK.</p>

        <p class="kota-tanggal">Surakarta, {{ ltrim($issued->format('d'), '0').' '.$namaBulan($issued).' '.$issued->format('Y') }}</p>

        <div class="signatures">
            <table class="ttd">
                <tr>
                    <td>
                        <p>Peserta,</p>
                        @if(!empty($agreement->participant_signature))
                            <img src="{{ $agreement->participant_signature }}" alt="Tanda tangan {{ $dosenUser->name ?? '' }}">
                        @else
                            <div class="sig-slot"></div>
                        @endif
                        <p class="nama">{{ $dosenUser->name ?? '........................' }}</p>
                        <p>NIDN: {{ $dosen->nidn ?? '—' }}</p>
                    </td>
                    <td>
                        <p>Mentor,</p>
                        @if(!empty($agreement->mentor_signature))
                            <img src="{{ $agreement->mentor_signature }}" alt="Tanda tangan {{ $mentorUser->name ?? '' }}">
                        @else
                            <div class="sig-slot"></div>
                        @endif
                        <p class="nama">{{ $mentorUser->name ?? '........................' }}</p>
                        <p>NIK: {{ $mentor->nik ?? '—' }}</p>
                    </td>
                </tr>
            </table>
        </div>
    </main>
</body>
</html>
