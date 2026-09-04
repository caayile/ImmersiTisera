<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login-choice');
    }

    public function showUserLogin()
    {
        return redirect()->route('login');
    }

    public function showMentorLogin()
    {
        return redirect()->route('login');
    }

    public function loginUser(Request $request)
    {
        return $this->attemptLogin($request, 'user');
    }

    public function loginMentor(Request $request)
    {
        return $this->attemptLogin($request, 'mentor');
    }

    public function showRegister()
    {
        return view('auth.register-choice', [
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function showUserRegister()
    {
        return redirect()->route('register');
    }

    public function showMentorRegister()
    {
        return redirect()->route('register');
    }

    public function registerUser(Request $request)
    {
        return $this->createAccount($request, 'participant');
    }

    public function registerMentor(Request $request)
    {
        return $this->createAccount($request, 'mentor');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = Password::sendResetLink($request->only('email'));

        return back()->with('status', __($status));
    }

    public function showReset(string $token)
    {
        return view('auth.reset', ['token' => $token, 'email' => request('email')]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    private function attemptLogin(Request $request, string $type)
    {
        $side = $type === 'mentor' ? 'mentor' : 'dosen';
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('auth_side', $side);
        }

        $credentials = $validator->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email')->with('auth_side', $side);
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (! $user->isActive()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun dinonaktifkan.'])->with('auth_side', $side);
        }

        if ($type === 'mentor' && ! $user->isMentor()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun ini bukan mentor. Peserta dan admin masuk dari kolom Dosen.'])->with('auth_side', $side);
        }

        if ($type === 'user' && $user->isMentor()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun mentor. Silakan masuk dari kolom Mentor.'])->with('auth_side', $side);
        }

        return redirect()->intended(route($user->homeRoute()));
    }

    private function createAccount(Request $request, string $role)
    {
        $side = $role === 'mentor' ? 'mentor' : 'dosen';
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
        if ($role === 'mentor') {
            $rules['department_id'] = ['nullable', 'exists:departments,id'];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('auth_side', $side);
        }

        $data = $validator->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $role,
            'phone' => $data['phone'] ?? null,
            'status' => 'active',
            'verification_status' => 'verified',
        ]);

        if ($role === 'participant') {
            Participant::create(['user_id' => $user->id]);
        } else {
            Mentor::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'] ?? null,
            ]);
        }

        Auth::login($user);

        return redirect()->route($user->homeRoute());
    }
}
