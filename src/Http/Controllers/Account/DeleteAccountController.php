<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use SteadfastCollective\StatamicAuth\Events\UserDeleted;

class DeleteAccountController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = Auth::user();
        
        Auth::logout();
        
        $user->delete();

        UserDeleted::dispatch($user);

        return redirect()
            ->route('auth.login')
            ->with('success', __('statamic-auth::strings.account.delete_account.success'));
    }
}