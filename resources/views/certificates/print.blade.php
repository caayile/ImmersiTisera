<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat {{ $certificate->number }}</title>
    <link rel="stylesheet" href="{{ asset('fonts/pinyon-script.css') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            margin: 0; 
            font-family: 'Arial', 'Helvetica', sans-serif; 
            background: #e8f2ef; 
            padding: 2rem 1rem;
        }
        
        .toolbar { 
            display: flex; 
            justify-content: flex-end; 
            gap: .75rem; 
            max-width: 1200px; 
            margin: 0 auto 1.5rem; 
        }
        .toolbar a, .toolbar button { 
            border: none; 
            border-radius: 10px; 
            padding: .85rem 1.5rem; 
            background: #0d5442; 
            color: #fff; 
            text-decoration: none; 
            cursor: pointer; 
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(13, 84, 66, 0.3);
            transition: all 0.3s ease;
        }
        .toolbar a:hover, .toolbar button:hover {
            background: #0a4033;
            box-shadow: 0 6px 16px rgba(13, 84, 66, 0.4);
            transform: translateY(-2px);
        }
        
        /* Container untuk sertifikat dengan aspek rasio landscape */
        .certificate-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
        }
        
        /* Background image sertifikat */
        .certificate-background {
            width: 100%;
            height: auto;
            display: block;
        }
        
        /* Overlay container untuk teks dinamis */
        .certificate-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        /* Nomor sertifikat - posisi di bawah judul SERTIFIKAT */
        .cert-number {
            position: absolute;
            top: 28.5%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 17px;
            font-weight: 600;
            color: #0d5442;
            letter-spacing: 0.8px;
            text-align: center;
        }
        
        /* Nama dosen dengan Pinyon Script - posisi tengah */
        .cert-name {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translateX(-50%);
            font-family: 'Pinyon Script', cursive;
            font-size: 64px;
            color: #0d5442;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            line-height: 1;
        }
        
        /* Garis bawah nama */
        .cert-name-underline {
            position: absolute;
            top: 51%;
            left: 26.5%;
            width: 47%;
            height: 2px;
            background: #0d5442;
        }
        
        /* Teks "Atas partisipasinya sebagai" */
        .cert-participation-intro {
            position: absolute;
            top: 54.5%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 16px;
            color: #1a1a1a;
            text-align: center;
        }
        
        /* Teks partisipasi bold - departemen dan unit bisnis */
        .cert-participation-detail {
            position: absolute;
            top: 58.5%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 16px;
            font-weight: 700;
            color: #0d5442;
            text-align: center;
            max-width: 85%;
            line-height: 1.5;
        }
        
        /* Tanggal dan lokasi */
        .cert-date {
            position: absolute;
            top: 67%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 16px;
            font-weight: 700;
            color: #0d5442;
            text-align: center;
        }
        
        /* Label mentor - kiri bawah */
        .cert-mentor-label {
            position: absolute;
            bottom: 24%;
            left: 27%;
            transform: translateX(-50%);
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.4;
        }
        
        /* Tanda tangan mentor - kiri bawah */
        .cert-signature-mentor {
            position: absolute;
            bottom: 12%;
            left: 27%;
            transform: translateX(-50%);
            text-align: center;
            width: 180px;
        }
        
        .cert-signature-mentor img {
            max-height: 70px;
            max-width: 180px;
            object-fit: contain;
            margin-bottom: 0.5rem;
        }
        
        .cert-signature-mentor .name {
            font-size: 15px;
            font-weight: 700;
            color: #0d5442;
            margin-bottom: 0.25rem;
        }
        
        /* Label CoE Manager - kanan bawah */
        .cert-coe-label {
            position: absolute;
            bottom: 24%;
            right: 19%;
            transform: translateX(50%);
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            color: #1a1a1a;
            line-height: 1.4;
        }
        
        /* Nama CoE Manager - kanan bawah */
        .cert-coe-name {
            position: absolute;
            bottom: 9.5%;
            right: 19%;
            transform: translateX(50%);
            text-align: center;
        }
        
        .cert-coe-name .name {
            font-size: 15px;
            font-weight: 700;
            color: #0d5442;
        }
        
        @media print {
            body { 
                background: #fff; 
                padding: 0;
            }
            .toolbar { 
                display: none !important; 
            }
            .certificate-wrapper { 
                box-shadow: none; 
                margin: 0; 
                max-width: none; 
            }
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
        
        @media (max-width: 768px) {
            .cert-name {
                font-size: 42px;
            }
            .cert-participation-detail {
                font-size: 14px;
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
@php
    $program = $certificate->program;
    $participant = $program->participant;
    $mentor = $program->mentor;
    $department = $program->department;
    $businessUnit = $program->businessUnit;
    $issuedDate = $certificate->issued_at ?? now();
    
    // Path ke gambar template sertifikat
    // Simpan gambar sertifikat di: public/images/certificates/template.png
    $templatePath = asset('images/certificates/template.png');
@endphp

<div class="toolbar">
    <a href="{{ url()->previous() }}">← Kembali</a>
    <button type="button" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="certificate-wrapper">
    <!-- Background image template sertifikat -->
    <img src="{{ $templatePath }}" alt="Template Sertifikat" class="certificate-background" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
    
    <!-- Fallback jika gambar belum ada -->
    <div style="display: none; padding: 4rem; background: linear-gradient(135deg, #d4ebe3 0%, #e8f4f0 50%, #d4ebe3 100%); text-align: center; color: #0d5442;">
        <h2 style="margin-bottom: 1rem;">⚠️ Template Sertifikat Belum Tersedia</h2>
        <p>Simpan gambar template sertifikat di: <code style="background: #fff; padding: 0.25rem 0.5rem; border-radius: 4px;">public/images/certificates/template.png</code></p>
    </div>
    
    <!-- Overlay dengan data dinamis -->
    <div class="certificate-overlay">
        <!-- Nomor Sertifikat -->
        <div class="cert-number">
            NO. {{ $certificate->number }}
        </div>
        
        <!-- Nama Dosen dengan Pinyon Script -->
        <div class="cert-name">
            {{ $participant->user->name }}
        </div>
        
        <!-- Garis bawah nama -->
        <div class="cert-name-underline"></div>
        
        <!-- Teks "Atas partisipasinya sebagai" -->
        <div class="cert-participation-intro">
            Atas partisipasinya sebagai
        </div>
        
        <!-- Teks Partisipasi Detail (Bold) -->
        <div class="cert-participation-detail">
            Peserta Magang Dosen Profesional di ({{ $department?->name ?? 'DEPARTEMEN' }}) pada ({{ $businessUnit?->name ?? 'UNIT BISNIS' }})
        </div>
        
        <!-- Tanggal dan Lokasi -->
        <div class="cert-date">
            Surakarta, {{ $issuedDate->locale('id')->isoFormat('D MMMM Y') }}
        </div>
        
        <!-- Label Mentor (Kiri) -->
        <div class="cert-mentor-label">
            ({{ $department?->name ?? 'DEPARTEMEN' }}) Mentor<br>({{ $businessUnit?->name ?? 'UNIT BISNIS' }})
        </div>
        
        <!-- Tanda Tangan Mentor (Kiri) -->
        <div class="cert-signature-mentor">
            @if($certificate->mentor_signature)
                <img src="{{ $certificate->mentor_signature }}" alt="Tanda tangan mentor">
            @endif
            <div class="name">({{ $mentor?->user?->name ?? 'Nama Mentor' }})</div>
        </div>
        
        <!-- Label CoE Manager (Kanan) -->
        <div class="cert-coe-label">
            Center of Excellence Manager<br>PT Tiga Serangkai Inti Corpora
        </div>
        
        <!-- Nama CoE Manager (Kanan) -->
        <div class="cert-coe-name">
            <div class="name">Erfan Zaenuddin</div>
        </div>
    </div>
</div>

</body>
</html>
