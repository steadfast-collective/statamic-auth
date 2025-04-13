<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers;

use Statamic\Auth\User;
use Statamic\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use SteadfastCollective\StatamicAuth\Events\UserLoggedIn;
use SteadfastCollective\StatamicAuth\Events\UserLoggedOut;
use SteadfastCollective\StatamicAuth\Http\Requests\LoginRequest;
use SteadfastCollective\StatamicAuth\Http\Controllers\AuthController;

class LoginController extends AuthController
{
    public function index(): View
    {
        return (new View)
            ->layout($this->layout)
            ->template('statamic-auth::login')
            ->with([
                'title' => __('statamic-auth::strings.login.seo_title'),
            ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        $remember = $request->remember;

        if(! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => __('statamic-auth::strings.credentials_incorrect')
            ])->withInput();
        }

        $user = Auth::user();

        if(config('statamic-auth.two_factor.enabled', true) && $user->two_factor_enabled) {
            Auth::logout();
            
            $redirectUrl = redirect()->intended()->getTargetUrl();
            
            session([
                'auth.2fa.user_id' => $user->id,
                'auth.2fa.remember' => $remember,
                'auth.2fa.intended_url' => $redirectUrl != config('app.url') ? redirect()->intended()->getTargetUrl() : route(config('statamic-auth.redirect', 'auth.account.index')),
            ]);

            return redirect()->route('auth.2fa.challenge');

        }

        if ($user->is_super || $user->has_any_cp_role) {
            $routeName = 'statamic.cp.dashboard';
        } else {
            $routeName = config('statamic-auth.redirect', 'auth.account.index');
        }

        UserLoggedIn::dispatch($user);

        return redirect()->route($routeName);
    }

    public function destroy()
    {
        $user = Auth::user();
        
        Auth::logout();

        UserLoggedOut::dispatch($user);

        return redirect()->route('auth.login')->with([
            'success' => __('statamic-auth::strings.logged_out')
        ]);
    }
}