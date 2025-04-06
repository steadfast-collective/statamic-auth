<?php

return [
    /**
     * Layout file to use
     * Default: resources/views/layout.antlers.html
     */
    'layout' => 'layout',

    /**
     * Default Statamic role/s for new users
     * Supply role handles, i.e 'admin', 'customer'
     */
    'default_roles' => [],

    /**
     * On login, if user has following roles, redirect to CP
     * Super admins will *always* be redirected to the CP
     */
    'cp_roles' => [],

    /**
     * Redirect after register / login
     * Please use route name
     */
    // 'redirect' => route("auth.account.index"),
    'redirect' => '/',

    /**
     * Users can register.
     * Setting this to false will disable the register routes
     */
    'register' => [
        'enabled' => true,
        'prefix' => 'register',
    ],

    /**
     * Account area settings
     * setting 'enabled' to false will disable the account routes
     */
    'account' => [
        'enabled' => true,
        'prefix' => 'my-account',
        'layout' => 'layout',
        'users_can_delete_account' => true,
        'users_can_update_password' => true,
    ]
];