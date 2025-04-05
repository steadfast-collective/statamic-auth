<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Socialite;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use SteadfastCollective\StatamicAuth\Helpers\CreateUser;

class GoogleController extends SocialiteController
{
    protected $driver = "google";

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver($this->driver)->user();
        
        $user = User::where('email', $googleUser->email)
            ->first();

        if(!$user) {
            if(User::hasSeparateNameFields()) {
                $user = CreateUser::create(
                    email: $googleUser->email,
                    password: Str::random(24),
                    first_name: $googleUser->user['given_name'],
                    last_name: $googleUser->user['family_name']
                );
            } else {
                $user = CreateUser::create(
                    email: $googleUser->email,
                    password: Str::random(24),
                    name: $googleUser->name
                );
            }
        }

        return $this->login($user);

    }
}