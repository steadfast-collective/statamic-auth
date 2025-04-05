<?php

namespace SteadfastCollective\StatamicAuth\Helpers;

use App\Models\User;
use Statamic\Facades\Role;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public static function create(
        string $email,
        string $password,
        ?string $first_name = null,
        ?string $last_name = null,
        ?string $name = null
    ): User
    {
        if(User::hasSeparateNameFields()) {
            $names = [
                'first_name' => $first_name,
                'last_name' => $last_name
            ];
        } else {
            $names = [
                'name' => $name,
            ];
        }

        $user = User::create([
            ...$names,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Assign user (statamic) to default role/s
        $defaultRoles = config('statamic-auth.default_roles', []);

        if(!empty($defaultRoles)) {
            foreach($defaultRoles as $roleHandle) {
                $role = Role::find($roleHandle);

                if($role) {
                    $user->statamic->assignRole($role);
                }
            }

            $user->statamic->save();
        }

        return $user;
    }
}