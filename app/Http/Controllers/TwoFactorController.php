<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function showSetup(): RedirectResponse|View
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->two_factor_confirmed_at) {
            return redirect()->route('admin.dashboard');
        }

        if (! $user->two_factor_secret) {
            $user->forceFill(['two_factor_secret' => TwoFactorService::generateSecret()])->save();
        }

        return view('auth.two-factor-setup', [
            'secret' => (string) $user->two_factor_secret,
            'provisioningUri' => TwoFactorService::provisioningUri($user, (string) $user->two_factor_secret),
        ]);
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->two_factor_secret) {
            return redirect()->route('two-factor.setup');
        }

        $data = $request->validate(['code' => ['required', 'digits:6']]);

        if (! TwoFactorService::verifyCode((string) $user->two_factor_secret, $data['code'])) {
            return back()->withErrors(['code' => 'Kode autentikator tidak valid. Silakan coba lagi.'])->withInput();
        }

        $recoveryCodes = TwoFactorService::generateRecoveryCodes();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => TwoFactorService::hashRecoveryCodes($recoveryCodes),
        ])->save();

        session()->forget('two_factor_setup_required');

        return redirect()->route('admin.dashboard')
            ->with('status', 'Autentikasi dua langkah berhasil diaktifkan.')
            ->with('recovery_codes', $recoveryCodes);
    }

    public function showChallenge(): RedirectResponse|View
    {
        if (! session('two_factor:user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function challenge(Request $request): RedirectResponse
    {
        $userId = session('two_factor:user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $data = $request->validate(['code' => ['required', 'string', 'max:32']]);
        $user = User::query()->findOrFail($userId);

        if (! $user->isAdmin() || ! $user->two_factor_confirmed_at) {
            session()->forget(['two_factor:user_id', 'two_factor:remember']);

            return redirect()->route('login')->withErrors([
                'email' => 'Autentikasi dua langkah belum diaktifkan untuk akun ini.',
            ]);
        }

        if (TwoFactorService::verifyCode((string) $user->two_factor_secret, $data['code'])) {
            return $this->completeChallenge($request, $user);
        }

        $hashes = $user->two_factor_recovery_codes ?? [];
        $matchedHash = TwoFactorService::matchRecoveryCode($hashes, $data['code']);

        if ($matchedHash !== null) {
            $user->forceFill([
                'two_factor_recovery_codes' => array_values(array_filter(
                    $hashes,
                    fn (string $hash): bool => $hash !== $matchedHash,
                )),
            ])->save();

            return $this->completeChallenge($request, $user);
        }

        return back()->withErrors(['code' => 'Kode autentikator tidak valid. Silakan coba lagi.'])->withInput();
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'password' => ['required'],
            'code' => ['required', 'digits:6'],
        ]);

        if (! Hash::check($data['password'], (string) $user->password)
            || ! TwoFactorService::verifyCode((string) $user->two_factor_secret, $data['code'])) {
            return back()->withErrors(['code' => 'Kata sandi atau kode autentikator tidak valid.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('admin.dashboard')->with('status', 'Autentikasi dua langkah dinonaktifkan.');
    }

    private function completeChallenge(Request $request, User $user): RedirectResponse
    {
        $remember = (bool) session('two_factor:remember', false);

        session()->forget(['two_factor:user_id', 'two_factor:remember']);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
