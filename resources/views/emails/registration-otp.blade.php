<x-mail::message>
# Kode Verifikasi Pendaftaran

Halo,

Gunakan kode berikut untuk menyelesaikan pendaftaran akun Magang Dosen TSU dengan email **{{ $email }}**.

Kode OTP Anda adalah:

# {{ $otp }}

Kode ini berlaku selama **10 menit**. Jangan bagikan kode ini kepada siapapun.

Jika Anda tidak merasa mendaftar, abaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
