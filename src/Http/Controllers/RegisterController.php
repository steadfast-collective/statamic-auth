<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers;

use App\Models\User;
use Statamic\View\View;
use Statamic\Facades\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use SteadfastCollective\StatamicAuth\Events\UserRegistered;
use SteadfastCollective\StatamicAuth\Helpers\CreateUser;
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

        $user = CreateUser::create(
            ...$names,
            email: $request->email,
            password: $request->password
        );

        UserRegistered::dispatch($user);

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