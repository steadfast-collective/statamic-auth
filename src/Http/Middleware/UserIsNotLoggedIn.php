<?php

namespace SteadfastCollective\StatamicAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserIsNotLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if($user) {
            if($user->is_super || $user->hasAnyCpRole()) {
                $routeName = 'statamic.cp.dashboard';
            } else {
                $routeName = config('auth.redirect');
            }
            
            return redirect()->route($routeName);
        }

        return $next($request);
    }
}