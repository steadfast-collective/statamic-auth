<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Socialite;

use App\Models\User;
use Statamic\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use SteadfastCollective\StatamicAuth\Helpers\CreateUser;

abstract class SocialiteController extends Controller
{
    protected $driver;
    
    public function redirect()
    {
        return Socialite::driver($this->driver)->redirect();
    }

    public function login(User $user): RedirectResponse
    {
        Auth::login($user);
        
        return redirect(config('statamic-auth.redirect'));
    }

    abstract public function callback(): RedirectResponse;
}