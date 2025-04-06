<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Socialite;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use SteadfastCollective\StatamicAuth\Helpers\CreateUser;

class GithubController extends SocialiteController
{
    protected $driver = "github";

    public function callback(): RedirectResponse
    {
        $githubUser = Socialite::driver($this->driver)->user();
        
        $user = User::where('email', $githubUser->getEmail())
            ->first();

        if(!$user) {
            if(User::hasSeparateNameFields()) {
                $names = CreateUser::splitName($githubUser->getName());

                $user = CreateUser::create(
                    ...$names,
                    email: $githubUser->getEmail(),
                    password: Str::random(24),
                );
            } else {
                $user = CreateUser::create(
                    email: $githubUser->getEmail(),
                    password: Str::random(24),
                    name: $githubUser->getName()
                );
            }
        }

        return $this->login($user);

    }

    
}