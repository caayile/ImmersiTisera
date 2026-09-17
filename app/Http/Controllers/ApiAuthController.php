<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use App\Models\Participant;
use App\Models\User;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

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
            'user' => app(ApiPresenter::class)->user($user),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'role' => ['required', Rule::in(['user', 'participant', 'mentor'])],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $role = $data['role'] === 'user' ? 'participant' : $data['role'];

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
            'verification_status' => 'verified',
        ]);

        if ($user->isParticipant()) {
            Participant::create(['user_id' => $user->id]);
        } else {
            Mentor::create(['user_id' => $user->id]);
        }

        return response()->json([
            'token' => $user->createToken('imersi')->plainTextToken,
            'user' => app(ApiPresenter::class)->user($user),
        ], 201);
    }

    public function me(Request $request, ApiPresenter $presenter)
    {
        return response()->json($presenter->user($request->user()));
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
