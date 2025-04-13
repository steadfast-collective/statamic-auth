<?php

namespace SteadfastCollective\StatamicAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactorVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(config('statamic-auth.two_factor.enabled')) {
            
            $user = Auth::user();

            if ($user && $user->two_factor_enabled && !session('auth.2fa.verified')) {
                // Store intended URL
                session(['auth.2fa.intended_url' => $request->fullUrl()]);
                
                // Logout and redirect to 2FA challenge
                Auth::logout();
                session(['auth.2fa.user_id' => $user->id]);
                
                return redirect()->route('auth.2fa.challenge');
            }
        }

        return $next($request);
    }
}