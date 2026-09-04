<?php

namespace App\Http\Controllers;

use App\Models\Mentor;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'user' => $user->toApiUser(),
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
            'user' => $user->toApiUser(),
        ], 201);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->toApiUser());
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
