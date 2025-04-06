<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Socialite;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use SteadfastCollective\StatamicAuth\Helpers\CreateUser;

class FacebookController extends SocialiteController
{
    protected $driver = "facebook";

    public function callback(): RedirectResponse
    {
        $oathUser = Socialite::driver($this->driver)->user();
        
        $user = User::where('email', $oathUser->getEmail())
            ->first();

        if(!$user) {
            if(User::hasSeparateNameFields()) {
                $names = CreateUser::splitName($oathUser->getName());

                $user = CreateUser::create(
                    ...$names,
                    email: $oathUser->getEmail(),
                    password: Str::random(24),
                );
            } else {
                $user = CreateUser::create(
                    email: $oathUser->getEmail(),
                    password: Str::random(24),
                    name: $oathUser->getName()
                );
            }
        }

        return $this->login($user);

    }

    
}