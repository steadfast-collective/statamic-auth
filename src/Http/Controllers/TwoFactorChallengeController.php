<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers;

use App\Models\User;
use Statamic\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Emargareten\TwoFactor\TwoFactor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use SteadfastCollective\StatamicAuth\Http\Controllers\AuthController;
use SteadfastCollective\StatamicAuth\Http\Requests\TwoFactor\StoreConfirmationRequest;
use PragmaRX\Google2FALaravel\Google2FA;
use SteadfastCollective\StatamicAuth\Http\Requests\TwoFactor\VerifyChallengeRequest;

class TwoFactorChallengeController extends AuthController
{
    public function show(): View
    {
        return (new View)
            ->layout($this->layout)
            ->template('statamic-auth::2fa.challenge')
            ->with([
                'title' => __('statamic-auth::strings.account.two_factor.challenge.seo_title'),
            ]);
    }

    public function verify(VerifyChallengeRequest $request)
    {
        $userId = session('auth.2fa.user_id');
        
        if (!$userId) {
            return redirect()->route('auth.login');
        }

        $user = User::find($userId);

        if(!$user || !$user->two_factor_enabled) {
            return redirect()->route('auth.login');
        }

        if($request->code) {
            $valid = $user->verifyOTP($request->code);
            
            if(!$valid) {
                throw ValidationException::withMessages([
                    'code' => [__('statamic-auth::strings.validation.invalid_code')],
                ]);
            }

        } elseif ($request->recovery_code) {
            $valid = $user->verifyRecoveryCode($request->recovery_code);
        } else {
            return back()->withErrors([
                'code' => 'Please provide an authentication code.',
            ]);
        }
        
        Auth::login($user, session('auth.2fa.remember', false));
        
        $intendedUrl = session('auth.2fa.intended_url', route('auth.account.index'));

        session()->forget(['auth.2fa.user_id', 'auth.2fa.remember', 'auth.2fa.intended_url']);
        
        // Mark as verified for this session
        session(['auth.2fa.verified' => true]);
        
        return redirect()->to($intendedUrl);

    }
}