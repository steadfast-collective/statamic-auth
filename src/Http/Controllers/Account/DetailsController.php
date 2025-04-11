<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Account;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use SteadfastCollective\StatamicAuth\Events\AccountDetailsUpdated;
use SteadfastCollective\StatamicAuth\Http\Requests\Account\UpdateDetailsRequest;

class DetailsController extends Controller
{
    public function update(UpdateDetailsRequest $request): RedirectResponse
    {
        $user = Auth::user();

        if(User::hasSeparateNameFields()) {
            $names = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name
            ];
        } else {
            $names = [
                'name' => $request->name
            ];
        }

        $user->update([
            ...$names,
            'email' => $request->email
        ]);

        AccountDetailsUpdated::dispatch($user);

        return back()->with('success', __('statamic-auth::strings.account.details.update-success'));
    }
}