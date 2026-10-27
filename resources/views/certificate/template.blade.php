<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat {{ $certificate->number }}</title>
    <link rel="stylesheet" href="{{ asset('fonts/pinyon-script.css') }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            padding: 24px;
            background: #e9f1ef;
            color: #101820;
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            width: min(1123px, 100%);
            margin: 0 auto 16px;
        }

        .toolbar a, .toolbar button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border: 1px solid #176b69;
            border-radius: 3px;
            background: #176b69;
            color: #fff;
            font: 600 14px Arial, Helvetica, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar a { background: #fff; color: #176b69; }

        .certificate-wrap {
            width: min(1123px, 100%);
            margin: 0 auto;
            container-type: inline-size;
            filter: drop-shadow(0 12px 24px rgb(15 46 43 / 14%));
        }

        .certificate {
            position: relative;
            width: 100%;
            aspect-ratio: 1123 / 794;
            overflow: hidden;
            background: #fff;
            isolation: isolate;
        }

        .certificate::before {
            position: absolute;
            z-index: -1;
            inset: 0 auto auto 0;
            width: 39%;
            height: 38%;
            background: linear-gradient(145deg, rgb(36 142 136 / 53%) 0 41%, rgb(101 185 176 / 42%) 41% 51%, transparent 51%);
            content: "";
            clip-path: polygon(0 0, 100% 0, 0 100%);
        }

        .certificate::after {
            position: absolute;
            z-index: -1;
            right: 0;
            bottom: 0;
            width: 38%;
            height: 43%;
            background: linear-gradient(320deg, rgb(39 149 144 / 66%) 0 35%, rgb(104 190 179 / 37%) 35% 48%, transparent 48%);
            content: "";
            clip-path: polygon(100% 0, 100% 100%, 0 100%);
        }

        .building {
            position: absolute;
            z-index: -2;
            right: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 26%;
            object-fit: cover;
            object-position: center 60%;
            opacity: .38;
            filter: grayscale(1) contrast(.82);
        }

        .frame {
            position: absolute;
            inset: 2.7% 2.25%;
            border: 1px solid rgb(26 112 108 / 82%);
            clip-path: polygon(0 0, 96.5% 0, 100% 5%, 100% 95%, 96.8% 100%, 0 100%);
        }

        .brand-row {
            position: absolute;
            top: 5.4%;
            left: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2.1cqw;
            width: 48%;
            height: 8.5%;
            transform: translateX(-50%);
        }

        .brand-logo-ts { width: 9.6cqw; height: 6.7cqw; object-fit: contain; }
        .brand-logo-tsu { width: 10.8cqw; height: 5.1cqw; object-fit: contain; }
        .brand-logo-magang { width: 13.2cqw; height: 5.2cqw; object-fit: contain; }

        .title {
            position: absolute;
            top: 16.4%;
            left: 50%;
            width: 82%;
            transform: translateX(-50%);
            color: #126a68;
            font: 700 6.15cqw/.95 Georgia, 'Times New Roman', serif;
            text-align: center;
        }

        .number {
            position: absolute;
            top: 25.5%;
            left: 50%;
            width: 75%;
            transform: translateX(-50%);
            color: #073c69;
            font: 700 2.05cqw/1.1 'Times New Roman', Times, serif;
            text-align: center;
        }

        .recipient-intro {
            position: absolute;
            top: 32.8%;
            left: 50%;
            transform: translateX(-50%);
            font: 400 2.3cqw/1.2 Georgia, 'Times New Roman', serif;
            white-space: nowrap;
        }

        .recipient-name {
            position: absolute;
            top: 38.3%;
            left: 50%;
            max-width: 88%;
            transform: translateX(-50%);
            color: #071e55;
            font: 400 min(73px, 6.5cqw)/1 'Pinyon Script', cursive;
            text-align: center;
            white-space: nowrap;
        }

        .recipient-rule {
            position: absolute;
            top: 49.8%;
            left: 26.5%;
            width: 47%;
            height: .18cqw;
            background: #202020;
        }

        .participation-intro {
            position: absolute;
            top: 53.1%;
            left: 50%;
            transform: translateX(-50%);
            font: 400 2.05cqw/1.2 Georgia, 'Times New Roman', serif;
            white-space: nowrap;
        }

        .participation-detail {
            position: absolute;
            top: 57.4%;
            left: 50%;
            width: 86%;
            transform: translateX(-50%);
            color: #131b24;
            font: 700 1.95cqw/1.35 Georgia, 'Times New Roman', serif;
            text-align: center;
        }

        .issued-date {
            position: absolute;
            top: 66%;
            left: 50%;
            transform: translateX(-50%);
            font: 700 1.48cqw/1.2 Arial, Helvetica, sans-serif;
            text-align: center;
            white-space: nowrap;
        }

        .mentor-label, .signatory-label {
            position: absolute;
            bottom: 20.2%;
            width: 34%;
            color: #141414;
            font: 600 1.48cqw/1.4 Arial, Helvetica, sans-serif;
            text-align: center;
        }

        .mentor-label { left: 10%; }
        .signatory-label { right: 7%; bottom: 25.2%; }

        .mentor-signature, .signatory-signature {
            position: absolute;
            bottom: 5.1%;
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: flex-end;
            width: 34%;
            height: 15%;
            text-align: center;
        }

        .mentor-signature { left: 10%; }
        .signatory-signature { right: 7%; }

        .mentor-signature img, .signatory-signature img {
            display: block;
            max-width: 68%;
            max-height: 62%;
            margin-bottom: .3cqw;
            object-fit: contain;
        }

        .mentor-signature img { max-width: 86%; max-height: 82%; }
        .signatory-signature img { max-width: 100%; max-height: 100%; }
        .signatory-signature img.signatory-seal { transform: scale(1.5); transform-origin: center bottom; }

        .mentor-name, .signatory-name {
            color: #111820;
            font: 700 1.48cqw/1.2 Arial, Helvetica, sans-serif;
        }

        @media (max-width: 640px) {
            body { padding: 12px 6px; }
            .toolbar a, .toolbar button { min-height: 36px; padding: 0 10px; font-size: 12px; }
            .brand-row { width: 56%; }
            .brand-logo-tsu { width: 11.5cqw; }
            .brand-logo-magang { width: 14cqw; }
            .participation-detail { width: 90%; font-size: 1.85cqw; }
        }

        @media print {
            @page { size: A4 landscape; margin: 0; }
            html, body { width: 100%; height: 100%; min-height: 0; }
            body { padding: 0; background: #fff; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .toolbar { display: none !important; }
            .certificate-wrap { width: 100vw; max-width: none; margin: 0; filter: none; }
            .certificate { width: 100vw; height: 70.72vw; max-height: 100vh; }
        }
    </style>
</head>
<body>
@php
    $program = $certificate->program;
    $participant = $program->participant;
    $mentor = $program->mentor;
    $issuedDate = $certificate->issued_at ?? now();
    $snapshot = $certificate->snapshot ?? [];
    $signatory = $certificate->signatory_snapshot ?? [];
    $participantName = $snapshot['participant_name'] ?? $participant->user?->name ?? 'Nama Dosen';
    $departmentName = $snapshot['department_name'] ?? $program->department?->name ?? 'DEPARTEMEN';
    $businessUnitName = $snapshot['business_unit_name'] ?? $program->businessUnit?->name ?? 'UNIT BISNIS';
    $mentorName = $snapshot['mentor_name'] ?? $mentor?->user?->name ?? 'Nama Mentor';
@endphp

<div class="toolbar">
    <a href="{{ url()->previous() }}">Kembali</a>
    <button type="button" onclick="window.print()">Cetak</button>
</div>

<main class="certificate-wrap">
    <section class="certificate" aria-label="Sertifikat {{ $participantName }}">
        <img class="building" src="{{ asset('images/sertifikat/image.png') }}" alt="">
        <div class="frame" aria-hidden="true"></div>

        <div class="brand-row" aria-label="Tiga Serangkai, TSU, Magang Dosen TSU">
            <img class="brand-logo-ts" src="{{ asset('images/sertifikat/Logo-TS-1024x720.png') }}" alt="Tiga Serangkai">
            <img class="brand-logo-tsu" src="{{ asset('images/sertifikat/LOGO-TSU-Web-Icon-1024x501.png') }}" alt="Tiga Serangkai University">
            <img class="brand-logo-magang" src="{{ asset('images/sertifikat/magang-dosen-tsu.png') }}" alt="Magang Dosen TSU">
        </div>

        <h1 class="title">SERTIFIKAT</h1>
        <p class="number">{{ $certificate->number }}</p>
        <p class="recipient-intro">Diberikan kepada:</p>
        <p class="recipient-name">{{ $participantName }}</p>
        <div class="recipient-rule" aria-hidden="true"></div>
        <p class="participation-intro">Atas partisipasinya sebagai</p>
        <p class="participation-detail">Peserta Magang Dosen Profesional di {{ $departmentName }} pada {{ $businessUnitName }}</p>
        <p class="issued-date">Surakarta, {{ $issuedDate->locale('id')->isoFormat('D MMMM Y') }}</p>

        <p class="mentor-label">{{ $departmentName }} Mentor<br>{{ $businessUnitName }}</p>
        <div class="mentor-signature">
            @if($signatureSource)
                <img src="{{ $signatureSource }}" alt="Tanda tangan mentor">
            @endif
            <span class="mentor-name">{{ $mentorName }}</span>
        </div>

        <p class="signatory-label">{{ $signatory['title'] ?? 'Center of Excellence Manager' }}<br>{{ $signatory['organization'] ?? 'PT Tiga Serangkai Inti Corpora' }}</p>
        <div class="signatory-signature">
            @if(($signatory['name'] ?? 'Erfan Zaenuddin') === 'Erfan Zaenuddin')
                <img class="signatory-seal" src="{{ asset('images/sertifikat/ttd-erfan-cap-transparan.png') }}" alt="Tanda tangan dan cap Erfan Zaenuddin">
            @elseif($signatorySignatureSource)
                <img src="{{ $signatorySignatureSource }}" alt="Tanda tangan pejabat penandatangan">
            @endif
            <span class="signatory-name">{{ $signatory['name'] ?? 'Erfan Zaenuddin' }}</span>
        </div>
    </section>
</main>
</body>
</html>