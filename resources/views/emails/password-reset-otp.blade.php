<x-mail::message>
# Kode Reset Kata Sandi

Halo,

Anda menerima email ini karena ada permintaan reset kata sandi untuk akun Magang Dosen TSU dengan email **{{ $email }}**.

Kode OTP Anda adalah:

# {{ $otp }}

Kode ini berlaku selama **10 menit**. Jangan bagikan kode ini kepada siapapun.

Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
