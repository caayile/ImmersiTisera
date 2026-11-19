<?php

namespace App\Http\Controllers;

use App\Http\Resources\Api\UserResource;
use App\Mail\RegistrationOtpMail;
use App\Models\Participant;
use App\Models\RegistrationOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Throwable;

class ApiAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi tidak sesuai.'],
            ]);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'Akun dinonaktifkan.'], 403);
        }

        $user->tokens()->delete();

        return response()->json([
            'token' => $user->createToken('imersi')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $email = Str::lower(trim($data['email']));

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages(['email' => 'Email sudah terdaftar.']);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        RegistrationOtp::updateOrCreate(
            ['email' => $email],
            [
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'role' => 'participant',
                'phone' => $data['phone'] ?? null,
                'otp' => null,
                'otp_hash' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        try {
            Mail::to($email)->send(new RegistrationOtpMail($otp, $email));
        } catch (Throwable) {
            RegistrationOtp::query()->where('email', $email)->delete();

            throw ValidationException::withMessages(['email' => 'Gagal mengirim email OTP. Silakan coba lagi.']);
        }

        return response()->json([
            'message' => 'Kode OTP 6 digit telah dikirim ke email Anda.',
            'email' => $email,
        ], 202);
    }

    public function verifyRegistration(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Str::lower(trim($data['email']));
        $pending = RegistrationOtp::query()
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pending || $pending->hasExceededAttempts() || ! $pending->matchesOtp($data['otp'])) {
            $pending?->registerFailedAttempt();

            throw ValidationException::withMessages(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
        }

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $pending->delete();

            throw ValidationException::withMessages(['email' => 'Email sudah terdaftar.']);
        }

        $user = DB::transaction(function () use ($pending): User {
            $user = User::create([
                'name' => $pending->name,
                'email' => $pending->email,
                'password' => $pending->password,
                'role' => 'participant',
                'phone' => $pending->phone,
                'status' => 'active',
                'verification_status' => 'verified',
            ]);

            Participant::create(['user_id' => $user->id]);
            $pending->delete();

            return $user;
        });

        return response()->json([
            'token' => $user->createToken('imersi')->plainTextToken,
            'user' => new UserResource($user),
        ], 201);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }
}
