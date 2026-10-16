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
            background: #e8f4f0; 
            padding: 2rem 1rem;
        }
        
        .toolbar { 
            display: flex; 
            justify-content: flex-end; 
            gap: .75rem; 
            max-width: 1100px; 
            margin: 0 auto 1.5rem; 
        }
        .toolbar a, .toolbar button { 
            border: none; 
            border-radius: 10px; 
            padding: .85rem 1.5rem; 
            background: linear-gradient(135deg, #1e7e5f 0%, #2a9d72 100%); 
            color: #fff; 
            text-decoration: none; 
            cursor: pointer; 
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(30, 126, 95, 0.3);
            transition: all 0.3s ease;
        }
        .toolbar a:hover, .toolbar button:hover {
            background: linear-gradient(135deg, #165a45 0%, #1e7e5f 100%);
            box-shadow: 0 6px 16px rgba(30, 126, 95, 0.4);
            transform: translateY(-2px);
        }
        
        .certificate-container {
            max-width: 1100px;
            margin: 0 auto;
            background: linear-gradient(135deg, #d4ebe3 0%, #e8f4f0 50%, #d4ebe3 100%);
            border: 15px solid;
            border-image: linear-gradient(135deg, #2a9d72, #1e7e5f, #2a9d72) 1;
            box-shadow: 
                0 0 0 3px #fff,
                0 0 0 6px #2a9d72,
                0 20px 60px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
        }
        
        /* Decorative corners */
        .certificate-container::before,
        .certificate-container::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(42, 157, 114, 0.15) 0%, transparent 70%);
        }
        .certificate-container::before {
            top: -100px;
            left: -100px;
        }
        .certificate-container::after {
            bottom: -100px;
            right: -100px;
        }
        
        .certificate-inner {
            background: linear-gradient(180deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.98) 100%);
            padding: 3.5rem 4rem;
            position: relative;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #2a9d72;
        }
        
        .header-logos {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .header-logos img {
            height: 60px;
            width: auto;
            object-fit: contain;
        }
        
        .title-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .certificate-title {
            font-size: 64px;
            font-weight: 700;
            color: #0d5442;
            letter-spacing: 4px;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }
        
        .certificate-number {
            font-size: 16px;
            font-weight: 600;
            color: #2a9d72;
            letter-spacing: 1px;
        }
        
        .content-section {
            text-align: center;
            margin: 2.5rem 0;
            line-height: 1.8;
        }
        
        .given-to {
            font-size: 16px;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .participant-name {
            font-family: 'Pinyon Script', cursive;
            font-size: 56px;
            color: #0d5442;
            font-weight: 400;
            margin: 1.5rem 0;
            text-decoration: underline;
            text-decoration-color: #2a9d72;
            text-decoration-thickness: 2px;
            text-underline-offset: 8px;
        }
        
        .participation-text {
            font-size: 15px;
            color: #333;
            margin: 1.5rem 0;
            line-height: 1.9;
        }
        
        .participation-text strong {
            color: #0d5442;
            font-weight: 700;
        }
        
        .date-location {
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            color: #0d5442;
            margin: 2rem 0 3rem;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            gap: 3rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #d4ebe3;
        }
        
        .signature-block {
            flex: 1;
            text-align: center;
        }
        
        .signature-label {
            font-size: 14px;
            color: #555;
            margin-bottom: 0.75rem;
        }
        
        .signature-image {
            height: 80px;
            margin: 0.5rem auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .signature-image img {
            max-height: 80px;
            max-width: 200px;
            object-fit: contain;
        }
        
        .signature-name {
            font-size: 15px;
            font-weight: 700;
            color: #0d5442;
            text-decoration: underline;
            text-decoration-color: #2a9d72;
            margin-bottom: 0.25rem;
        }
        
        .signature-title {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }
        
        @media print {
            body { 
                background: #fff; 
                padding: 0;
            }
            .toolbar { 
                display: none !important; 
            }
            .certificate-container { 
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
            .certificate-inner {
                padding: 2rem 1.5rem;
            }
            .certificate-title {
                font-size: 42px;
            }
            .participant-name {
                font-size: 38px;
            }
            .header-logos {
                flex-direction: column;
                gap: 1rem;
            }
            .header-logos img {
                height: 40px;
            }
            .signatures {
                flex-direction: column;
                gap: 2rem;
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
@endphp

<div class="toolbar">
    <a href="{{ url()->previous() }}">← Kembali</a>
    <button type="button" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="certificate-container">
    <div class="certificate-inner">
        <!-- Header dengan Logo -->
        <div class="header">
            <div class="header-logos">
                <img src="{{ asset('images/logo-tsu.png') }}" alt="TSU Logo">
                <img src="{{ asset('images/logo-full.png') }}" alt="Tiga Serangkai Logo">
            </div>
            <div class="header-logos">
                <img src="{{ asset('images/logo-mark.png') }}" alt="Magang Dosen TSU Logo">
            </div>
        </div>
        
        <!-- Title -->
        <div class="title-section">
            <h1 class="certificate-title">SERTIFIKAT</h1>
            <p class="certificate-number">NO. {{ $certificate->number }}</p>
        </div>
        
        <!-- Content -->
        <div class="content-section">
            <p class="given-to">Diberikan kepada:</p>
            
            <h2 class="participant-name">{{ $participant->user->name }}</h2>
            
            <p class="participation-text">
                Atas partisipasinya sebagai<br>
                <strong>Peserta Magang Dosen Profesional di {{ $department?->name ?? 'Department' }} pada {{ $businessUnit?->name ?? 'Business Unit' }}</strong>
            </p>
        </div>
        
        <!-- Date and Location -->
        <div class="date-location">
            Surakarta, {{ $issuedDate->locale('id')->isoFormat('D MMMM Y') }}
        </div>
        
        <!-- Signatures -->
        <div class="signatures">
            <!-- Mentor Signature (Left) -->
            <div class="signature-block">
                <p class="signature-label">({{ $department?->name ?? 'DEPARTEMEN' }}) Mentor<br>({{ $businessUnit?->name ?? 'UNIT BISNIS' }})</p>
                <div class="signature-image">
                    @if($certificate->mentor_signature)
                        <img src="{{ $certificate->mentor_signature }}" alt="Tanda tangan mentor">
                    @endif
                </div>
                <p class="signature-name">{{ $mentor?->user?->name ?? 'Nama Mentor' }}</p>
                <p class="signature-title"></p>
            </div>
            
            <!-- CoE Manager Signature (Right) -->
            <div class="signature-block">
                <p class="signature-label">Center of Excellence Manager<br>PT Tiga Serangkai Inti Corpora</p>
                <div class="signature-image">
                    <!-- Placeholder untuk tanda tangan CoE Manager -->
                    <!-- Bisa ditambahkan dari database jika diperlukan -->
                </div>
                <p class="signature-name">Erfan Zaenuddin</p>
                <p class="signature-title"></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
