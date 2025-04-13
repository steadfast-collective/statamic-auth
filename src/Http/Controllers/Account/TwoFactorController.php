<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Account;

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

class TwoFactorController extends AuthController
{
    protected $google2fa;
    
    public function __construct(Google2FA $google2fa)
    {
        parent::__construct();
        $this->google2fa = $google2fa;
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if($user->two_factor_enabled) {
            return back()->with('2fa_status', '2fa already enabled.');
        }

        $user->enableTwoFactorAuthentication();

        return redirect()->route('auth.account.2fa.confirm.show');
    }

    public function showConfirmation(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()
                ->route('auth.account.index')
                ->with('success', 'Two-factor authentication is already enabled');
        }

        if (! $user->two_factor_secret) {
            return redirect()
                ->route('auth.account.index')
                ->with('error', 'Two-factor authentication is not enabled');
        }

        return (new View)
            ->layout($this->layout)
            ->template('statamic-auth::2fa.confirm')
            ->with([
                'title' => __('statamic-auth::strings.account.two_factor.confirm_page.seo_title'),
                'qrCodeSvg' => $user->twoFactorQrCodeSvg(),
                'setupKey' => $user->two_factor_secret,
            ]);
    }

    public function storeConfirmation(StoreConfirmationRequest $request): RedirectResponse
    {
        $user = Auth::user();
        
        if (empty($user->two_factor_secret) || empty($request->code)) {
            throw ValidationException::withMessages([
                'code' => [__('statamic-auth::strings.validation.invalid_code')],
            ]);
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => [__('statamic-auth::strings.validation.invalid_code')],
            ]);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now()
        ])->save();

        return redirect()->route('auth.account.index')
            ->with(
                'success', 
                __('statamic-auth::strings.account.two_factor.activated_success')
            )
            ->with(
                'showRecoveryCodes',
                true
            );
    }

    public function destroy(): RedirectResponse
    {
        $user = Auth::user();

        $user->disableTwoFactorAuthentication();

        return redirect()->route('auth.account.index')->with(
            'success', 
            __('statamic-auth::strings.account.two_factor.deactivated_success')
        );
    }

    public function generateCodes(Request $request)
    {
        $user = Auth::user();

        $user->forceFill([
            'two_factor_recovery_codes' => $user->createRecoveryCodes(),
            'two_factor_recovery_code_last_used' => null
        ])->save();

        return redirect()->route('auth.account.index')
            ->with(
                'success', 
                __('statamic-auth::strings.account.two_factor.recovery_codes_updated')
            )
            ->with(
                'showRecoveryCodes',
                true
            );
    }
}