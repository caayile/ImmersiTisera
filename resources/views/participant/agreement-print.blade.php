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
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 18px; }
        .kop h1 { margin: 0; font-size: 16pt; letter-spacing: .04em; text-align: center; }
        .kop p { margin: 4px 0 0; font-style: italic; font-size: 11pt; text-align: center; }
        .judul { margin: 18px 0 14px; }
        .judul h2 { margin: 0; font-size: 13pt; text-align: center; }
        .judul p { margin: 4px 0 0; font-size: 11pt; text-align: center; }
        p, li, td, th { font-size: 11pt; line-height: 1.65; color: #000; }
        p.isi { margin: 0 0 10px; text-align: justify; }
        .pasal-title { margin: 20px 0 2px; font-weight: bold; text-align: center; }
        .pasal-no { margin: 0 0 8px; font-weight: bold; text-align: center; }
        .identitas { width: 100%; border-collapse: collapse; margin: 8px 0 12px; }
        .identitas td { vertical-align: top; padding: 2px 4px; text-align: left; }
        .ttd { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 28px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 12px; }
        .ttd td p { text-align: center; margin: 2px 0; }
        .ttd img { display: block; height: 120px; width: 100%; object-fit: contain; margin: 8px auto 4px; }
        .ttd .nama { font-weight: bold; text-decoration: underline; margin: 2px 0; text-align: center; }
        .catatan { margin-top: 26px; font-size: 9.5pt; font-style: italic; font-family: Arial, sans-serif; text-align: justify; }
        @media (max-width: 640px) { .toolbar { margin: 1rem; } .paper { margin: 0; padding: 2rem 1.3rem; } }
        @page { size: A4; margin: 4cm 3cm 3cm 4cm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .paper { max-width: none; margin: 0; padding: 0; }
            * { color: #000 !important; box-shadow: none !important; text-shadow: none !important; }
            a { text-decoration: none !important; }
        }
        @if(($pdf ?? false))
        .toolbar { display: none !important; }
        .paper { max-width: none; margin: 0; padding: 0; }
        @endif
    </style>
</head>
<body>
@php
    $isPdf = $pdf ?? false;
    $hariId = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
    $bulanId = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
    $issued = $agreement->letter_issued_at ?? $agreement->mentor_approved_at ?? now();
    $hari = $hariId[$issued->format('l')] ?? $issued->format('l');
    $tgl = $issued->format('d');
    $bln = $bulanId[(int) $issued->format('n')] ?? $issued->format('F');
    $thn = $issued->format('Y');
    $dosen = $program->participant;
    $dosenUser = $dosen->user ?? null;
    $mentor = $program->mentor;
    $mentorUser = $mentor->user ?? null;
    $unitBisnis = $program->businessUnit->name ?? '-';
    $departemen = $program->department->name ?? '-';
    $noPendaftaran = $program->application->letter_number ?? ($program->application_id ? 'APP-'.$program->application_id : '-');
    $mulai = $program->start_date ? $program->start_date->format('d').' '.$bulanId[(int) $program->start_date->format('n')].' '.$program->start_date->format('Y') : '-';
    $selesai = $program->end_date ? $program->end_date->format('d').' '.$bulanId[(int) $program->end_date->format('n')].' '.$program->end_date->format('Y') : '-';
    $indicators = collect($agreement->success_indicators ?? [])->filter()->values();
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
        <div class="kop">
            <h1>PROGRAM MAGANG DOSEN TSU</h1>
            <p>Surat Perjanjian Kerja Sama Pelaksanaan Program</p>
        </div>

        <div class="judul">
            <h2>PERJANJIAN MAGANG DOSEN</h2>
            <p>No. {{ $agreement->letter_number ?? '____/MD/TSU/TS/__-____' }}</p>
        </div>

        <p class="isi">Pada hari ini, {{ $hari }}, tanggal {{ $tgl }} bulan {{ $bln }} tahun {{ $thn }}, kami yang bertanda tangan di bawah ini masing-masing:</p>

        <table class="identitas">
            <tr><td style="width:130px">Nama</td><td style="width:12px">:</td><td>{{ $dosenUser->name ?? '-' }}</td></tr>
            <tr><td>NIDN</td><td>:</td><td>{{ $dosen->nidn ?? '-' }}</td></tr>
            <tr><td>Fakultas</td><td>:</td><td>{{ $dosen->faculty ?? '-' }}</td></tr>
            <tr><td>Program Studi</td><td>:</td><td>{{ $dosen->study_program ?? '-' }}</td></tr>
        </table>
        <p class="isi">Dalam hal ini bertindak untuk dan atas nama diri sendiri selaku Dosen peserta Program Magang Dosen TSU, selanjutnya disebut sebagai <strong>&ldquo;PIHAK PERTAMA&rdquo;</strong>.</p>

        <table class="identitas">
            <tr><td style="width:130px">Nama</td><td style="width:12px">:</td><td>{{ $mentorUser->name ?? '-' }}</td></tr>
            <tr><td>Jabatan</td><td>:</td><td>{{ $mentor->position ?? 'Mentor Industri' }}</td></tr>
            <tr><td>Unit Bisnis</td><td>:</td><td>{{ $unitBisnis }}</td></tr>
            <tr><td>Departemen</td><td>:</td><td>{{ $departemen }}</td></tr>
        </table>
        <p class="isi">Dalam hal ini bertindak untuk dan atas nama Unit Bisnis tersebut selaku Mentor pendamping, selanjutnya disebut sebagai <strong>&ldquo;PIHAK KEDUA&rdquo;</strong>.</p>

        <p class="isi">PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama selanjutnya disebut &ldquo;PARA PIHAK&rdquo;, dan secara sendiri-sendiri disebut &ldquo;Pihak&rdquo;.</p>
        <p class="isi">PARA PIHAK terlebih dahulu menerangkan hal-hal sebagai berikut:</p>
        <p class="isi" style="padding-left:18px">a.&nbsp;&nbsp; bahwa PIHAK PERTAMA merupakan dosen tetap yang telah dinyatakan diterima mengikuti Program Magang Dosen TSU melalui proses pendaftaran dan tinjauan yang disahkan oleh Admin Program;</p>
        <p class="isi" style="padding-left:18px">b.&nbsp;&nbsp; bahwa PIHAK KEDUA merupakan pengelola/perwakilan Unit Bisnis yang bersedia menjadi mitra pelaksanaan program serta menyediakan bimbingan (mentoring) bagi PIHAK PERTAMA;</p>
        <p class="isi" style="padding-left:18px">c.&nbsp;&nbsp; bahwa PARA PIHAK sepakat untuk menuangkan kesepakatan pelaksanaan Program Magang Dosen ke dalam Perjanjian ini dengan ketentuan-ketentuan sebagai berikut:</p>

        <p class="pasal-title">DASAR PELAKSANAAN</p>
        <p class="pasal-no">PASAL 1</p>
        <p class="isi">Perjanjian ini dilaksanakan berdasarkan pendaftaran Program Magang Dosen TSU yang telah disahkan oleh Admin Program dan disetujui oleh Mentor pada Unit Bisnis terkait, dengan Nomor Pendaftaran: {{ $noPendaftaran }}.</p>

        <p class="pasal-title">RUANG LINGKUP DAN TUJUAN</p>
        <p class="pasal-no">PASAL 2</p>
        <p class="isi">(1) Tujuan bersama:<br>{{ $agreement->objective ?? '-' }}</p>
        <p class="isi">(2) Aktivitas dan ruang lingkup kerja:<br>{{ $agreement->activities ?? '-' }}</p>
        <p class="isi">(3) Permasalahan atau peluang yang menjadi fokus:<br>{{ $agreement->problem_statement ?? '-' }}</p>

        <p class="pasal-title">JANGKA WAKTU</p>
        <p class="pasal-no">PASAL 3</p>
        <p class="isi">Perjanjian ini berlaku terhitung sejak tanggal {{ $mulai }} sampai dengan tanggal {{ $selesai }}, dan hanya dapat diperpanjang, diubah, atau diakhiri lebih awal atas kesepakatan tertulis PARA PIHAK melalui sistem Program Magang Dosen TSU.</p>

        <p class="pasal-title">LUARAN UTAMA DAN INDIKATOR KEBERHASILAN</p>
        <p class="pasal-no">PASAL 4</p>
        <p class="isi">(1) Luaran utama yang disepakati:<br>{{ $agreement->main_output ?? '-' }}</p>
        <p class="isi">(2) Indikator keberhasilan program (maksimal 3):</p>
        @if($indicators->isNotEmpty())
            @foreach($indicators->take(3) as $i => $ind)
                <p class="isi" style="padding-left:18px">{{ $i + 1 }}) {{ $ind }}</p>
            @endforeach
        @else
            <p class="isi" style="padding-left:18px">1) -<br>2) -<br>3) -</p>
        @endif

        <p class="pasal-title">HAK DAN KEWAJIBAN PIHAK PERTAMA</p>
        <p class="pasal-no">PASAL 5</p>
        <p class="isi">PIHAK PERTAMA berkewajiban untuk:</p>
        <p class="isi" style="padding-left:18px">a. melaksanakan aktivitas magang sesuai ruang lingkup sebagaimana diatur dalam Pasal 2;</p>
        <p class="isi" style="padding-left:18px">b. mengisi logbook aktivitas secara berkala selama periode magang;</p>
        <p class="isi" style="padding-left:18px">c. mengikuti sesi mentoring mingguan yang dijadwalkan oleh PIHAK KEDUA;</p>
        <p class="isi" style="padding-left:18px">d. menyerahkan luaran utama dan laporan akhir sesuai dengan tenggat waktu yang disepakati;</p>
        <p class="isi" style="padding-left:18px">e. menjaga kerahasiaan data dan informasi milik PIHAK KEDUA yang diperoleh selama pelaksanaan program.</p>

        <p class="pasal-title">HAK DAN KEWAJIBAN PIHAK KEDUA</p>
        <p class="pasal-no">PASAL 6</p>
        <p class="isi">PIHAK KEDUA berkewajiban untuk:</p>
        <p class="isi" style="padding-left:18px">a. menunjuk Mentor guna membimbing PIHAK PERTAMA selama periode magang;</p>
        <p class="isi" style="padding-left:18px">b. menyediakan akses data, fasilitas, dan informasi yang diperlukan sesuai ruang lingkup pekerjaan;</p>
        <p class="isi" style="padding-left:18px">c. meninjau logbook dan luaran kerja, serta memberikan penilaian/evaluasi atas pelaksanaan program;</p>
        <p class="isi" style="padding-left:18px">d. memberikan umpan balik dan/atau permintaan revisi kepada PIHAK PERTAMA apabila diperlukan.</p>

        <p class="pasal-title">MANFAAT</p>
        <p class="pasal-no">PASAL 7</p>
        <p class="isi">(1) Manfaat bagi PIHAK PERTAMA:<br>{{ $agreement->participant_benefit ?? '-' }}</p>
        <p class="isi">(2) Manfaat bagi PIHAK KEDUA:<br>{{ $agreement->business_benefit ?? '-' }}</p>

        <p class="pasal-title">KERAHASIAAN</p>
        <p class="pasal-no">PASAL 8</p>
        <p class="isi">PARA PIHAK sepakat untuk menjaga kerahasiaan seluruh data, dokumen, dan informasi yang diperoleh selama pelaksanaan program, serta tidak akan mengungkapkannya kepada pihak ketiga tanpa persetujuan tertulis dari Pihak lainnya, kecuali diwajibkan oleh peraturan perundang-undangan yang berlaku.</p>

        <p class="pasal-title">POTENSI KOLABORASI LANJUTAN</p>
        <p class="pasal-no">PASAL 9</p>
        <p class="isi">PARA PIHAK dapat menindaklanjuti hasil pelaksanaan program ini dalam bentuk kerja sama lanjutan, dengan arah/bentuk kolaborasi sebagai berikut:<br>{{ $agreement->collaboration_potential ?: '-' }}<br>yang akan diatur lebih lanjut melalui kesepakatan atau perjanjian tersendiri apabila diperlukan.</p>

        <p class="pasal-title">PERUBAHAN DAN REVISI</p>
        <p class="pasal-no">PASAL 10</p>
        <p class="isi">Setiap perubahan atau revisi atas isi Perjanjian ini hanya berlaku apabila diajukan dan disetujui secara tertulis/digital oleh PARA PIHAK melalui sistem Program Magang Dosen TSU.</p>

        <p class="pasal-title">PENYELESAIAN PERSELISIHAN</p>
        <p class="pasal-no">PASAL 11</p>
        <p class="isi">Apabila terjadi perselisihan dalam pelaksanaan Perjanjian ini, PARA PIHAK sepakat untuk menyelesaikannya terlebih dahulu secara musyawarah untuk mufakat. Apabila musyawarah tidak mencapai kesepakatan, PARA PIHAK sepakat menyelesaikannya melalui mekanisme yang ditetapkan oleh pengelola Program Magang Dosen TSU.</p>

        <p class="pasal-title">PENUTUP</p>
        <p class="pasal-no">PASAL 12</p>
        <p class="isi">Demikian Perjanjian ini dibuat dan disetujui oleh PARA PIHAK dalam rangkap 2 (dua) atau secara elektronik melalui sistem Program Magang Dosen TSU, dengan kekuatan hukum yang sama, untuk dipergunakan sebagaimana mestinya. Perjanjian ini dinyatakan berlaku dan Program Magang dinyatakan Aktif setelah disetujui/ditandatangani secara digital oleh PARA PIHAK.</p>

        <table class="ttd">
            <tr>
                <td>
                    <p>PIHAK PERTAMA,</p>
                    @if(!empty($agreement->participant_signature))
                        <img src="{{ $agreement->participant_signature }}" alt="Tanda tangan {{ $dosenUser->name ?? '' }}">
                    @else
                        <div style="height:120px"></div>
                    @endif
                    <p class="nama">( {{ $dosenUser->name ?? '........................' }} )</p>
                    <p>Dosen / Peserta Program</p>
                </td>
                <td>
                    <p>PIHAK KEDUA,</p>
                    @if(!empty($agreement->mentor_signature))
                        <img src="{{ $agreement->mentor_signature }}" alt="Tanda tangan {{ $mentorUser->name ?? '' }}">
                    @else
                        <div style="height:120px"></div>
                    @endif
                    <p class="nama">( {{ $mentorUser->name ?? '........................' }} )</p>
                    <p>Mentor / Perwakilan Unit Bisnis</p>
                </td>
            </tr>
        </table>

        <p class="catatan">Catatan: Dokumen ini merupakan template baku Perjanjian Magang Dosen. Seluruh data terisi otomatis sesuai data pendaftaran, kesepakatan kerja (Agreement), dan persetujuan digital PARA PIHAK di dalam sistem.</p>
    </main>
</body>
</html>
