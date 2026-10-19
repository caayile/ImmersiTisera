<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat {{ $certificate->number }}</title>
    <link rel="stylesheet" href="{{ asset('fonts/pinyon-script.css') }}">
    <style>
        @page { size: A4 landscape; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; padding: 24px; background: #e5e7eb; color: #000; }
        .tools { position: fixed; z-index: 2; top: 12px; right: 12px; }
        .tools button { padding: 8px 16px; border: 0; border-radius: 4px; background: #146a6c; color: #fff; cursor: pointer; }
        .certificate-wrap { width: min(1123px, 100%); margin: 0 auto; container-type: inline-size; }
        .certificate {
            position: relative;
            width: 100%;
            aspect-ratio: 1123 / 794;
            overflow: hidden;
            background: #fff url('{{ asset('images/certificate/bg-sertifikat.jpg') }}') center / 100% 100% no-repeat;
            color: #000;
            font-family: 'Tinos', 'Times New Roman', serif;
        }
        .absolute { position: absolute; left: 0; right: 0; text-align: center; white-space: nowrap; }
        .logos { position: absolute; top: 4.28%; left: 30.01%; width: 40%; }
        .title { top: 17.88%; color: #146a6c; font-size: 7.48cqw; font-weight: 700; line-height: 1; }
        .number { top: 28.21%; color: #17436b; font-size: 2.58cqw; font-weight: 700; }
        .given { top: 35.64%; font-size: 2.23cqw; }
        .name { top: 40.55%; left: 18.35%; width: 63.3%; color: #0f1f4d; font-family: 'Pinyon Script', cursive; font-size: 6.5cqw; line-height: 1.2; }
        .line { position: absolute; top: 51.64%; left: 26.18%; width: 45.33%; height: 2px; background: #000; }
        .as { top: 53.9%; font-size: 1.96cqw; }
        .role { top: 57.43%; left: 14.95%; width: 70.1%; font-size: 2.05cqw; font-weight: 700; }
        .date { top: 66.75%; font-family: Arial, sans-serif; font-size: 1.51cqw; font-weight: 700; }
        .sign-label, .sign-name { position: absolute; width: 29.38%; text-align: center; }
        .sign-label { top: 73.05%; font-family: Arial, sans-serif; font-size: 1.42cqw; font-weight: 700; line-height: 1.4; }
        .sign-name { top: 91.69%; font-family: Arial, sans-serif; font-size: 1.42cqw; font-weight: 700; }
        .left { left: 19.95%; }
        .right { left: 50.67%; }
        .mentor-signature { position: absolute; top: 81.6%; left: 24.5%; width: 20.2%; height: 9.4%; object-fit: contain; object-position: center bottom; }
        .signatory-signature { position: absolute; top: 78.34%; left: 56.99%; width: 20.21%; max-height: 16%; object-fit: contain; }
        .fit { white-space: nowrap; }
        @media (max-width: 640px) {
            body { padding: 12px 6px; }
            .certificate-wrap { width: 100%; }
            .tools button { padding: 7px 10px; font-size: 12px; }
        }
        @media print {
            html, body { width: 100%; height: 100%; min-height: 0; }
            body { padding: 0; background: #fff; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .tools { display: none; }
            .certificate-wrap { width: 100vw; max-width: none; }
            .certificate { width: 100vw; height: 70.7vw; }
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
    $signatoryName = $signatory['name'] ?? 'Erfan Zaenuddin';
@endphp

<div class="tools"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>

<main class="certificate-wrap">
    <section class="certificate" aria-label="Sertifikat {{ $participantName }}">
        <img class="logos" src="{{ asset('images/certificate/logo-header.png') }}" alt="Tiga Serangkai, TSU, Magang Dosen TSU">
        <h1 class="absolute title">SERTIFIKAT</h1>
        <p class="absolute number">{{ $certificate->number }}</p>
        <p class="absolute given">Diberikan kepada:</p>
        <p class="absolute name fit" data-max="6.5">{{ $participantName }}</p>
        <div class="line" aria-hidden="true"></div>
        <p class="absolute as">Atas partisipasinya sebagai</p>
        <p class="absolute role fit" data-max="2.05">Peserta Magang Dosen Profesional di {{ strtoupper($departmentName) }} pada {{ strtoupper($businessUnitName) }}</p>
        <p class="absolute date">Surakarta, {{ $issuedDate->locale('id')->translatedFormat('j F Y') }}</p>

        <p class="sign-label left">{{ strtoupper($departmentName) }} Mentor<br>{{ strtoupper($businessUnitName) }}</p>
        @if($signatureSource)
            <img class="mentor-signature" src="{{ $signatureSource }}" alt="Tanda tangan mentor">
        @endif
        <p class="sign-name left">{{ $mentorName }}</p>

        <p class="sign-label right">{{ $signatory['title'] ?? 'Center of Excellence Manager' }}<br>{{ $signatory['organization'] ?? 'PT Tiga Serangkai Inti Corpora' }}</p>
        @if($signatoryName === 'Erfan Zaenuddin')
            <img class="signatory-signature" src="{{ asset('images/certificate/ttd-cap-erfan.png') }}" alt="Tanda tangan dan cap Erfan Zaenuddin">
        @elseif($signatorySignatureSource)
            <img class="signatory-signature" src="{{ $signatorySignatureSource }}" alt="Tanda tangan pejabat penandatangan">
        @endif
        <p class="sign-name right">{{ $signatoryName }}</p>
    </section>
</main>

<script>
    document.fonts.ready.then(() => {
        document.querySelectorAll('.fit').forEach((element) => {
            const text = element.textContent;
            let size = Number(element.dataset.max);
            const span = document.createElement('span');
            span.textContent = text;
            span.style.display = 'inline-block';
            element.textContent = '';
            element.appendChild(span);
            element.style.fontSize = `${size}cqw`;

            while (span.offsetWidth > element.clientWidth && size > 1.1) {
                size -= 0.05;
                element.style.fontSize = `${size}cqw`;
            }
        });
    });
</script>
</body>
</html>