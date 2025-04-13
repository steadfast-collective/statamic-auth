<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Account;

use App\Models\User;
use Statamic\View\View;
use App\Http\Controllers\Controller;
use Emargareten\TwoFactor\TwoFactor;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        return (new View)
            ->layout(config('statamic-auth.account.layout', 'layout'))
            ->template('statamic-auth::account.index')
            ->with([
                'title' => __('statamic-auth::strings.account.seo_title'),
                'separate_name_fields' => User::hasSeparateNameFields(),
                '2fa_enabled' => $user->two_factor_enabled,
                'recovery_code_count' => collect($user->two_factor_recovery_codes)->count()
            ]);
    }
}