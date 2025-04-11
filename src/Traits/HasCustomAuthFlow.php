<?php

namespace SteadfastCollective\StatamicAuth\Traits;

use Statamic\Facades\User as StatamicUser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use SteadfastCollective\StatamicAuth\Notifications\ResetPasswordNotification;

trait HasCustomAuthFlow 
{
    public function statamic(): Attribute
    {
        return Attribute::make(
            get: function() {
                return StatamicUser::fromUser($this);
            }
        );
    }

    public function hasAnyCpRole(): Attribute
    {
        return Attribute::make(
            get: function() {
                $cpRoles = config('statamic-auth.cp_roles', []);
                return !empty($cpRoles) && !empty(array_intersect($this->statamic->roles, $cpRoles));
            }
        );
    }

    /**
     * Send a password reset notification to the user.
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $url = route('auth.password.reset', [
            'token' => $token,
            'email' => $this->email
        ]);

        $this->notify(new ResetPasswordNotification($url, $this));
    }

    public static function hasSeparateNameFields()
    {
        $fields = StatamicUser::blueprint()->fields()->all();
        return $fields->has('first_name') && $fields->has('last_name');
    }
}