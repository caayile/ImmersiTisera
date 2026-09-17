<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login-choice', [
            'initialTab' => 'login',
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
        ]);
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
            'initialTab' => 'register',
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

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if ($canonicalRedirect = $this->canonicalizeLocalGoogleHost($request)) {
            return $canonicalRedirect;
        }

        $role = $this->googleOauthRole($request->query('role'));
        session(['oauth_role' => $role]);

        if ($this->googleOauthIsConfigured()) {
            return Socialite::driver('google')->redirect();
        }

        if (! app()->environment(['local', 'testing'])) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google belum dikonfigurasi.',
            ]);
        }

        return $this->loginWithDemoGoogleUser($request, $role);
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login Google dibatalkan. Silakan coba lagi.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            Log::error('Google OAuth callback failed: '.$exception->getMessage(), [
                'exception' => $exception,
            ]);

            return redirect()->route('login')->withErrors([
                'email' => $this->googleOauthErrorMessage($exception),
            ]);
        }

        if (empty($googleUser->getEmail())) {
            return redirect()->route('login')->withErrors(['email' => 'Akun Google Anda tidak menyediakan alamat email yang valid.']);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar() ?? $user->avatar,
            ]);
        } else {
            $role = $this->googleOauthRole(session('oauth_role'));

            $user = User::create([
                'name' => $googleUser->getName() ?: ($googleUser->getNickname() ?: 'Pengguna Google'),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'role' => $role,
                'status' => 'active',
                'verification_status' => 'verified',
            ]);

            $this->ensureGoogleProfile($user, $role);
        }

        if (! $user->isActive()) {
            return redirect()->route('login')->withErrors(['email' => 'Akun dinonaktifkan.']);
        }

        session()->forget('oauth_role');
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route($user->homeRoute());
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
            'institution' => ['nullable', 'string', 'max:180'],
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
            $institution = trim((string) ($data['institution'] ?? ''));

            Participant::create([
                'user_id' => $user->id,
                'profile_data' => $institution !== '' ? ['institution' => $institution] : null,
            ]);
        } else {
            Mentor::create([
                'user_id' => $user->id,
                'department_id' => $data['department_id'] ?? null,
            ]);
        }

        Auth::login($user);

        return redirect()->route($user->homeRoute());
    }

    private function googleOauthIsConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    private function googleOauthRole(mixed $role): string
    {
        return $role === 'mentor' ? 'mentor' : 'participant';
    }

    private function canonicalizeLocalGoogleHost(Request $request): ?RedirectResponse
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $requestHost = $request->getHost();

        if (! is_string($appHost) || $appHost === $requestHost) {
            return null;
        }

        if (! in_array($appHost, ['localhost', '127.0.0.1'], true) || ! in_array($requestHost, ['localhost', '127.0.0.1'], true)) {
            return null;
        }

        return redirect()->away(rtrim((string) config('app.url'), '/').$request->getRequestUri());
    }

    private function loginWithDemoGoogleUser(Request $request, string $role): RedirectResponse
    {
        $demoEmail = $role === 'mentor' ? 'mentor.google@imersi.id' : 'dosen.google@imersi.id';
        $demoName = $role === 'mentor' ? 'Mentor Industri (Google)' : 'Dr. Dosen Akademik (Google)';

        $user = User::firstOrCreate(
            ['email' => $demoEmail],
            [
                'name' => $demoName,
                'google_id' => 'google-demo-'.$role,
                'avatar' => 'https://ui-avatars.com/api/?name='.urlencode($demoName).'&background=0D221D&color=73D9B0',
                'role' => $role,
                'status' => 'active',
                'verification_status' => 'verified',
            ]
        );

        $this->ensureGoogleProfile($user, $role);

        session()->forget('oauth_role');
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route($user->homeRoute())->with('status', 'Berhasil masuk dengan Akun Google ('.$user->name.').');
    }

    private function googleOauthErrorMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'invalid_client') || str_contains($message, 'client secret is invalid')) {
            return 'Client secret Google tidak valid. Salin Client secret yang aktif dari Google Cloud Console (APIs & Services → Credentials → OAuth 2.0 Client) ke GOOGLE_CLIENT_SECRET di .env, simpan, lalu coba lagi.';
        }

        if (str_contains($message, 'redirect_uri_mismatch')) {
            return 'Redirect URI Google tidak cocok. Tambahkan '.url('/auth/google/callback').' ke Authorized redirect URIs di Google Cloud Console.';
        }

        if (str_contains($message, 'Invalid state')) {
            return 'Sesi login Google kedaluwarsa. Silakan klik Sambung dengan Google lagi.';
        }

        return 'Gagal menghubungkan akun Google. Silakan coba lagi.';
    }

    private function ensureGoogleProfile(User $user, string $role): void
    {
        if ($role === 'mentor' && ! $user->mentor) {
            Mentor::create(['user_id' => $user->id]);
        }

        if ($role === 'participant' && ! $user->participant) {
            Participant::create(['user_id' => $user->id]);
        }
    }
}
