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

    public static function splitName(
        string $fullName
    ): array
    {
        // Trim and remove extra spaces
        $fullName = trim(preg_replace("/\s+/", " ", $fullName));

        // If name is empty, return empty values
        if (empty($fullName)) {
            return ["first_name" => "", "last_name" => ""];
        }

        // Split the name by spaces
        $nameParts = explode(" ", $fullName);

        // If only one part exists, it's considered the first name
        if (count($nameParts) == 1) {
            return [
                "first_name" => $nameParts[0],
                "last_name" => ""
            ];
        }

        // Get the last name (last part)
        $lastName = array_pop($nameParts);

        // All remaining parts form the first name (including middle name)
        $firstName = implode(" ", $nameParts);

        return [
            "first_name" => $firstName,
            "last_name" => $lastName
        ];
    }
}