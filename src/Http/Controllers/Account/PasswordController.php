<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use SteadfastCollective\StatamicAuth\Http\Requests\Account\UpdatePasswordRequest;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        try {
            $user = Auth::user();

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return back()->with(
                'success', 
                __('statamic-auth::strings.account.password.update-success')
            );
        } catch (\Exception $e) {
            return back()->with(
                'error', 
                __('statamic-auth::strings.account.password.update-error')
            );
        }
    }
}