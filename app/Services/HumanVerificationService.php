<?php

namespace App\Services;

use Illuminate\Http\Request;

class HumanVerificationService
{
    /**
     * Validasi honeypot dan token checkbox reCAPTCHA v2 yang dikirim formulir.
     */
    public static function verify(Request $request): bool
    {
        // Honeypot check: bot_check_field harus selalu kosong (diisi otomatis oleh script bot jahat)
        if ($request->filled('bot_check_field')) {
            return false;
        }

        // Test otomatis bawaan tidak mengirim token reCAPTCHA, jadi lewati verifikasi Google.
        if (app()->environment('testing') && ! $request->has('g-recaptcha-response')) {
            return true;
        }

        return RecaptchaService::verify(
            (string) $request->input('g-recaptcha-response', ''),
            $request->ip(),
        );
    }
}
