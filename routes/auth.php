<?php

use Illuminate\Support\Facades\Route;
use SteadfastCollective\StatamicAuth\Mail\PasswordReset;
use SteadfastCollective\StatamicAuth\Http\Controllers\LoginController;
use SteadfastCollective\StatamicAuth\Http\Middleware\UserIsNotLoggedIn;
use SteadfastCollective\StatamicAuth\Http\Controllers\PasswordController;
use SteadfastCollective\StatamicAuth\Http\Controllers\RegisterController;
use SteadfastCollective\StatamicAuth\Http\Controllers\Socialite\GoogleController;

Route::group([
    'as' => 'auth.',
    'middleware' => ['web']
], function() {

    Route::middleware([UserIsNotLoggedIn::class])->group(function() {
        Route::group([
            'prefix' => 'login'
        ], function() {
            Route::get('/', [LoginController::class, 'index'])->name('login');
            Route::post('/', [LoginController::class, 'store'])->name('login.store');
        });

        Route::group([
            'as' => 'password.'
        ], function() {
            Route::get('forgot-password', [PasswordController::class, 'showForgotPasswordForm'])->name('request');
            Route::post('forgot-password', [PasswordController::class, 'sendResetLink'])->name('email');
            Route::get('link-sent', [PasswordController::class, 'linkSent'])->name('link-sent');
            Route::get('reset-password/{token}', [PasswordController::class, 'showResetForm'])->name('reset');
            Route::post('reset-password', [PasswordController::class, 'reset'])->name('update');
            Route::get('recovery-success', [PasswordController::class, 'recoverySuccess'])->name('recovery-success');
        });

        if(config('statamic-auth.register.enabled', true)) {
            Route::group([
                'as' => 'register.',
                'prefix' => config('statamic-auth.register.prefix', 'register')
            ], function() {
                Route::get('/', [RegisterController::class, 'index'])->name('index');
                Route::post('/', [RegisterController::class, 'store'])->name('store');
            });
        }

        // Socialite - Google
        if(config('services.google')) {
            Route::group([
                'prefix' => 'google',
                'as' => 'google.'
            ], function() {
                Route::get('redirect', [GoogleController::class, 'redirect'])->name('redirect');
                Route::get('callback', [GoogleController::class, 'callback'])->name('callback');
            });
        }
    });
    
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');


    if(config('statamic-auth.account.enabled', true)) {
        Route::group([
            'as' => 'account.',
            'prefix' => config('statamic-auth.account.prefix', 'my-account'),
        ], function() {

        });
    }
});

Route::group([
    'prefix' => 'mail'
], function() {
    Route::get('password-reset', function(){
        return new PasswordReset('https://example.com', \App\Models\User::first());
    });
});