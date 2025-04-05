<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers;

use App\Models\User;
use Statamic\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use SteadfastCollective\StatamicAuth\Http\Controllers\AuthController;
use SteadfastCollective\StatamicAuth\Http\Requests\ResetPasswordRequest;
use SteadfastCollective\StatamicAuth\Http\Requests\SendPasswordResetRequest;

class PasswordController extends AuthController
{
    public function showForgotPasswordForm(): View
    {
        return (new View)
            ->layout($this->layout)
            ->template('statamic-auth::forgotten_password')
            ->with([
                'title' => __('statamic-auth::strings.forgotten_password.seo_title'),
            ]);
    }

    public function sendResetLink(SendPasswordResetRequest $request)
    {
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? redirect((route('auth.password.link-sent')))
            : back()->withErrors(['email' => __($status)]);
    }

    public function linkSent(): View
    {
        return (new View)
            ->layout($this->layout)
            ->template('statamic-auth::link_sent')
            ->with([
                'title' => __('statamic-auth::strings.password_reset_link_sent.seo_title'),
            ]);
    }

    public function showResetForm(string $token, Request $request): Response
    {
        
        if(!$request->email) {
            return $this->returnPasswordResetErrorView('token_not_found');
        }

        $tokenRecord = DB::table(config('auth.passwords.users.table'))
            ->where('email', $request->email)
            ->first();

        if(!$tokenRecord) {
            return $this->returnPasswordResetErrorView('token_not_found');
        }

        $createdAt = Carbon::parse($tokenRecord->created_at);
        $expires = config('auth.passwords.users.expire', 60);

        if ($createdAt->addMinutes($expires)->isPast()) {
            return $this->returnPasswordResetErrorView('token_expired');
        }

        if (!Hash::check($token, $tokenRecord->token)) {
            return $this->returnPasswordResetErrorView('token_not_found');
        }

        $passwordRules = config('auth.passwords.rules');

        return response((new View)
            ->layout($this->layout)
            ->template('statamic-auth::reset_password')
            ->with([
                'title' => __('statamic-auth::strings.reset_password.seo_title'),
                'rules' => $passwordRules,
                'token' => $token
            ])
        );
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('auth.password.recovery-success')
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function recoverySuccess(): View
    {
        return (new View)
            ->layout($this->layout)
            ->template('statamic-auth::recovery-success')
            ->with([
                'title' => __('statamic-auth::strings.account_recovery_success.seo_title'),
            ]);
    }

    /**
     * Return a custom error view for password reset issues
     *
     * @param string $errorType 'token_not_found' or 'token_expired'
     * @return \Illuminate\Http\Response
     */
    private function returnPasswordResetErrorView(string $errorType)
    {
        $statusCode = $errorType === 'token_expired' ? 419 : 404;
        
        return response((new View)
            ->layout($this->layout)
            ->template('statamic-auth::reset_password_error')
            ->with([
                'title' => $errorType === 'token_expired' 
                    ? __('statamic-auth::strings.password_reset_token.expired.seo_title')
                    : __('statamic-auth::strings.password_reset_token.invalid.seo_title'),
                'error_type' => $errorType,
                'message' => $errorType === 'token_expired' 
                    ? __('statamic-auth::strings.password_reset_token.expired.message')
                    : __('statamic-auth::strings.password_reset_token.invalid.message')
            ]), $statusCode);

    }
}