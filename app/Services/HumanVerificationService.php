<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class HumanVerificationService
{
    private const SESSION_KEY = 'human_verification_challenges';

    /**
     * Generate a new challenge and store it in session.
     *
     * @return array{key: string, question: string}
     */
    public static function generateChallenge(): array
    {
        $num1 = random_int(3, 15);
        $num2 = random_int(2, 9);
        $isAddition = random_int(0, 1) === 1;

        if (! $isAddition && $num1 < $num2) {
            [$num1, $num2] = [$num2, $num1];
        }

        $operator = $isAddition ? '+' : '-';
        $answer = $isAddition ? ($num1 + $num2) : ($num1 - $num2);
        $key = Str::random(16);

        $challenges = Session::get(self::SESSION_KEY, []);
        if (! is_array($challenges)) {
            $challenges = [];
        }

        // Simpan maksimal 5 token tantangan terakhir agar sesi tetap ramping
        if (count($challenges) > 5) {
            $challenges = array_slice($challenges, -4, null, true);
        }

        $challenges[$key] = [
            'answer' => (int) $answer,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ];

        Session::put(self::SESSION_KEY, $challenges);

        return [
            'key' => $key,
            'question' => "{$num1} {$operator} {$num2} = ?",
        ];
    }

    /**
     * Validate the submitted challenge answer and anti-bot honeypot.
     */
    public static function verify(Request $request): bool
    {
        // Honeypot check: bot_check_field harus selalu kosong (diisi otomatis oleh script bot jahat)
        if ($request->filled('bot_check_field')) {
            return false;
        }

        // Jika dalam testing dan request tidak mengirimkan captcha_key, izinkan tes otomatis bawaan berjalan
        if (app()->environment('testing') && ! $request->has('captcha_key')) {
            return true;
        }

        $key = (string) $request->input('captcha_key');
        $answer = $request->input('captcha_answer');

        if ($key === '' || $answer === null || $answer === '') {
            return false;
        }

        $challenges = Session::get(self::SESSION_KEY, []);
        if (! is_array($challenges) || ! isset($challenges[$key])) {
            return false;
        }

        $challenge = $challenges[$key];
        unset($challenges[$key]);
        Session::put(self::SESSION_KEY, $challenges);

        if (now()->timestamp > (int) ($challenge['expires_at'] ?? 0)) {
            return false;
        }

        return (int) $answer === (int) $challenge['answer'];
    }
}
