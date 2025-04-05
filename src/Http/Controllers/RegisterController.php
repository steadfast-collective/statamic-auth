<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers;

use App\Models\User;
use Statamic\View\View;
use Statamic\Facades\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use SteadfastCollective\StatamicAuth\Http\Requests\RegisterRequest;
use SteadfastCollective\StatamicAuth\Http\Controllers\AuthController;

class RegisterController extends AuthController
{
    public function index(): View
    {
        return (new View)
            ->layout($this->layout)
            ->template('statamic-auth::register')
            ->with([
                'title' => __('statamic-auth::strings.register.seo_title'),
                'separate_name_fields' => User::hasSeparateNameFields(),
            ]);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        // Create user
        if(User::hasSeparateNameFields()) {
            $names = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name
            ];
        } else {
            $names = [
                'name' => $request->name,
            ];
        }

        $user = User::create([
            ...$names,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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

        $credentials = $request->only('email', 'password');

        if(! Auth::attempt($credentials, false)) {
            return back()->withErrors([
                'email' => __('statamic-auth::strings.credentials_incorrect'),
            ])->withInput();
        }

        $routeName = config('statamic-auth.redirect', 'auth.account.index');

        return redirect()->route($routeName);
    }
}