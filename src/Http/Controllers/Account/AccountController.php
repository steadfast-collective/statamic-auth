<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers\Account;

use App\Models\User;
use Statamic\View\View;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function __invoke(): View
    {
        return (new View)
            ->layout(config('statamic-auth.account.layout', 'layout'))
            ->template('statamic-auth::account.index')
            ->with([
                'title' => __('statamic-auth::strings.account.seo_title'),
                'separate_name_fields' => User::hasSeparateNameFields(),
            ]);
    }
}