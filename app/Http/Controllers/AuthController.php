<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtpMail;
use App\Mail\RegistrationOtpMail;
use App\Models\Department;
use App\Models\HeroSetting;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\PasswordResetOtp;
use App\Models\RegistrationOtp;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login-choice', $this->loginViewData('login'));
    }

    public function showUserLogin()
    {
        return redirect()->route('login')->with('auth_side', 'dosen');
    }

    public function showMentorLogin()
    {
        return redirect()->route('login')->with('auth_side', 'mentor');
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
        return view('auth.register-choice', $this->loginViewData('register'));
    }

    public function showUserRegister()
    {
        return redirect()->route('register');
    }

    public function showMentorRegister()
    {
        return redirect()->route('register');
    }

    /**
     * @return array{initialTab: string, departments: Collection<int, Department>, heroImage: string, heroAlternateImage: string}
     */
    private function loginViewData(string $initialTab): array
    {
        return [
            'initialTab' => $initialTab,
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'heroImage' => HeroSetting::forHome()->backgroundUrl() ?: asset('images/hero/beranda.jpg'),
            'heroAlternateImage' => asset('images/hero/beranda-tiga-serangkai.jpg').'?v=2',
        ];
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if ($canonicalRedirect = $this->canonicalizeLocalGoogleHost($request)) {
            return $canonicalRedirect;
        }

        $role = $this->googleOauthRole($request->query('role'));
        session(['oauth_role' => $role]);

        if ($this->googleOauthIsConfigured()) {
            if (! $this->googleOauthPackageIsInstalled()) {
                return $this->googleOauthMissingPackageRedirect();
            }

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

        if (! $this->googleOauthPackageIsInstalled()) {
            return $this->googleOauthMissingPackageRedirect();
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

        $googleEmail = Str::lower(trim((string) $googleUser->getEmail()));
        $user = User::query()
            ->where(function ($query) use ($googleUser, $googleEmail): void {
                $query->where('google_id', $googleUser->getId())
                    ->orWhereRaw('LOWER(email) = ?', [$googleEmail]);
            })
            ->first();

        if (! $user) {
            $role = $this->googleOauthRole(session('oauth_role'));

            if ($pending = $this->pendingRegistrationRedirect($googleEmail, $role)) {
                return $pending;
            }

            return $this->unregisteredGoogleRedirect($role);
        }

        return $this->loginRegisteredGoogleUser(
            $request,
            $user,
            $this->googleOauthRole(session('oauth_role')),
            (string) $googleUser->getId(),
            $googleUser->getAvatar(),
        );
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

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('password.verify-otp', ['email' => $request->email])
                ->with('status', 'Jika email terdaftar, kode OTP 6 digit telah dikirim. Silakan periksa kotak masuk email Anda.');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::updateOrCreate(
            ['email' => $user->email],
            [
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
                'verified_at' => null,
            ]
        );

        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($otp, $user->email));
        } catch (Throwable $exception) {
            Log::error('Failed sending password reset OTP email: '.$exception->getMessage(), [
                'email' => $user->email,
                'exception' => $exception,
            ]);

            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP. Silakan coba lagi dalam beberapa saat atau hubungi admin.',
            ])->withInput();
        }

        return redirect()->route('password.verify-otp', ['email' => $user->email])
            ->with('status', 'Kode OTP 6 digit telah dikirim ke email Anda. Kode berlaku 10 menit.');
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

        $otpRecord = PasswordResetOtp::where('email', $request->email)
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $otpRecord) {
            return back()->withErrors(['email' => 'Sesi reset tidak valid atau sudah kedaluwarsa. Silakan ulangi proses.'])->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.'])->withInput();
        }

        $user->forceFill(['password' => $request->password, 'remember_token' => Str::random(60)])->save();
        event(new PasswordReset($user));

        $otpRecord->delete();

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
    }

    public function showVerifyOtp()
    {
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp_digits' => ['required', 'array', 'size:6'],
            'otp_digits.*' => ['required', 'digits:1'],
        ]);

        $otp = implode('', $request->otp_digits);

        $otpRecord = PasswordResetOtp::where('email', $request->email)
            ->where('otp', $otp)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $otpRecord) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.'])->withInput();
        }

        $otpRecord->update(['verified_at' => now()]);

        return redirect()->route('password.reset', [
            'token' => base64_encode($request->email.'|'.now()->timestamp),
            'email' => $request->email,
        ]);
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
        $email = Str::lower(trim($credentials['email']));

        if ($pending = $this->pendingRegistrationRedirect($email, $type === 'mentor' ? 'mentor' : 'participant')) {
            return $pending;
        }

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

    public function showRegisterOtp()
    {
        return view('auth.verify-otp', [
            'formAction' => route('register.verify-otp.submit'),
            'resendAction' => route('register.verify-otp.resend'),
            'changeEmailUrl' => route('register'),
            'otpHeading' => 'Verifikasi Email Pendaftaran',
        ]);
    }

    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp_digits' => ['required', 'array', 'size:6'],
            'otp_digits.*' => ['required', 'digits:1'],
        ]);

        $email = Str::lower(trim($request->string('email')->toString()));
        $otp = implode('', $request->input('otp_digits'));
        $pending = RegistrationOtp::query()
            ->where('email', $email)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->first();

        if (! $pending) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.'])->withInput();
        }

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $pending->delete();

            return redirect()->route('login')->withErrors(['email' => 'Email ini sudah terdaftar. Silakan masuk.']);
        }

        $user = DB::transaction(function () use ($pending): User {
            $user = User::create([
                'name' => $pending->name,
                'email' => $pending->email,
                'password' => $pending->password,
                'role' => $pending->role,
                'phone' => $pending->phone,
                'status' => 'active',
                'verification_status' => 'verified',
            ]);

            if ($pending->role === 'participant') {
                $institution = trim((string) $pending->institution);

                Participant::create([
                    'user_id' => $user->id,
                    'profile_data' => $institution !== '' ? ['institution' => $institution] : null,
                ]);
            } else {
                Mentor::create([
                    'user_id' => $user->id,
                    'nik' => $pending->nik,
                    'department_id' => $pending->department_id,
                ]);
            }

            $pending->delete();

            return $user;
        });

        $this->notifyAdminsOfRegistration($user);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($user->homeRoute())->with('status', 'Pendaftaran berhasil. Akun Anda sudah aktif.');
    }

    public function resendRegisterOtp(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $email = Str::lower(trim($request->string('email')->toString()));
        $pending = RegistrationOtp::query()->where('email', $email)->first();

        if (! $pending) {
            return redirect()->route('register')->withErrors([
                'email' => 'Pendaftaran tidak ditemukan. Silakan daftar ulang.',
            ]);
        }

        $otp = $this->makeOtpCode();

        if (! $this->sendRegistrationOtpMail($pending->email, $otp)) {
            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP. Silakan coba lagi dalam beberapa saat atau hubungi admin.',
            ]);
        }

        $pending->update([
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);

        return redirect()->route('register.verify-otp', ['email' => $pending->email])
            ->with('status', 'Kode OTP baru telah dikirim. Kode berlaku 10 menit.');
    }

    private function createAccount(Request $request, string $role)
    {
        $side = $role === 'mentor' ? 'mentor' : 'dosen';
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ];
        if ($role === 'mentor') {
            $rules['department_id'] = ['nullable', 'exists:departments,id'];
            $rules['nik'] = ['nullable', 'string', 'max:40'];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('auth_side', $side);
        }

        $data = $validator->validated();

        if (User::query()->whereRaw('LOWER(email) = ?', [$data['email']])->exists()) {
            return back()->withErrors(['email' => 'Email sudah terdaftar.'])->withInput()->with('auth_side', $side);
        }

        $otp = $this->makeOtpCode();
        $pending = RegistrationOtp::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'role' => $role,
                'phone' => null,
                'institution' => null,
                'nik' => $data['nik'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'otp' => $otp,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        if (! $this->sendRegistrationOtpMail($pending->email, $otp)) {
            $pending->delete();

            return back()->withErrors([
                'email' => 'Gagal mengirim email OTP. Silakan coba lagi dalam beberapa saat atau hubungi admin.',
            ])->withInput()->with('auth_side', $side);
        }

        return redirect()->route('register.verify-otp', ['email' => $pending->email])
            ->with('status', 'Kode OTP 6 digit telah dikirim ke email Anda. Kode berlaku 10 menit.');
    }

    private function googleOauthIsConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    private function googleOauthPackageIsInstalled(): bool
    {
        return class_exists(Socialite::class);
    }

    private function googleOauthMissingPackageRedirect(): RedirectResponse
    {
        return redirect()->route('login')->withErrors([
            'email' => $this->googleOauthMissingPackageMessage(),
        ]);
    }

    private function googleOauthMissingPackageMessage(): string
    {
        return 'Paket login Google belum terpasang. Di folder proyek jalankan composer install, lalu muat ulang halaman login.';
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
        $user = User::query()->whereRaw('LOWER(email) = ?', [$demoEmail])->first();

        if (! $user) {
            if ($pending = $this->pendingRegistrationRedirect($demoEmail, $role)) {
                return $pending;
            }

            return $this->unregisteredGoogleRedirect($role);
        }

        return $this->loginRegisteredGoogleUser(
            $request,
            $user,
            $role,
            status: 'Berhasil masuk dengan Akun Google ('.$user->name.').',
        );
    }

    private function unregisteredGoogleRedirect(string $role): RedirectResponse
    {
        session()->forget('oauth_role');

        return redirect()->route('register')->withErrors([
            'email' => 'Email Google ini belum terdaftar. Daftarkan email tersebut terlebih dahulu, lalu masuk dengan Google.',
        ])->with('auth_side', $role === 'mentor' ? 'mentor' : 'dosen');
    }

    private function loginRegisteredGoogleUser(Request $request, User $user, string $role, ?string $googleId = null, ?string $avatar = null, ?string $status = null): RedirectResponse
    {
        $side = $role === 'mentor' ? 'mentor' : 'dosen';

        if (! $user->isActive()) {
            session()->forget('oauth_role');

            return redirect()->route('login')->withErrors(['email' => 'Akun dinonaktifkan.'])->with('auth_side', $side);
        }

        if ($role === 'mentor' && ! $user->isMentor()) {
            session()->forget('oauth_role');

            return redirect()->route('login')->withErrors([
                'email' => 'Akun ini bukan mentor. Peserta dan admin masuk dari kolom Dosen.',
            ])->with('auth_side', 'mentor');
        }

        if ($role !== 'mentor' && $user->isMentor()) {
            session()->forget('oauth_role');

            return redirect()->route('login')->withErrors([
                'email' => 'Akun mentor. Silakan masuk dari kolom Mentor.',
            ])->with('auth_side', 'dosen');
        }

        $updates = [];

        if (filled($googleId)) {
            $updates['google_id'] = $googleId;
        }

        if (filled($avatar)) {
            $updates['avatar'] = $avatar;
        }

        if ($updates !== []) {
            $user->update($updates);
        }

        $this->ensureGoogleProfile($user, $user->role);

        session()->forget('oauth_role');
        Auth::login($user, true);
        $request->session()->regenerate();

        $redirect = redirect()->route($user->homeRoute());

        if ($status !== null) {
            return $redirect->with('status', $status);
        }

        return $redirect;
    }

    private function pendingRegistrationRedirect(string $email, string $role): ?RedirectResponse
    {
        $pending = RegistrationOtp::query()->where('email', Str::lower($email))->first();

        if (! $pending) {
            return null;
        }

        session()->forget('oauth_role');

        return redirect()->route('register.verify-otp', ['email' => $pending->email])->withErrors([
            'otp' => 'Selesaikan verifikasi OTP terlebih dahulu. Kode dikirim ke email pendaftaran.',
        ])->with('auth_side', $role === 'mentor' ? 'mentor' : 'dosen');
    }

    private function makeOtpCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendRegistrationOtpMail(string $email, string $otp): bool
    {
        try {
            Mail::to($email)->send(new RegistrationOtpMail($otp, $email));
        } catch (Throwable $exception) {
            Log::error('Gagal mengirim OTP pendaftaran: '.$exception->getMessage(), [
                'email' => $email,
                'exception' => $exception,
            ]);

            return false;
        }

        return true;
    }

    private function notifyAdminsOfRegistration(User $user): void
    {
        $title = $user->isMentor() ? 'Mentor baru mendaftar' : 'Dosen baru mendaftar';
        $message = $user->name.' ('.$user->email.') baru membuat akun.';
        $url = route('admin.users');

        User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get()
            ->each(function (User $admin) use ($title, $message, $url, $user): void {
                try {
                    $admin->notify(new ImersiAlert($title, $message, $url));
                } catch (Throwable $exception) {
                    Log::error('Gagal mengirim notifikasi pendaftaran ke admin: '.$exception->getMessage(), [
                        'admin_id' => $admin->id,
                        'user_id' => $user->id,
                    ]);
                }
            });
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

        if (str_contains($message, 'Socialite') && str_contains($message, 'not found')) {
            return $this->googleOauthMissingPackageMessage();
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
