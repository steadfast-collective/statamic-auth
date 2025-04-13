<?php

use Illuminate\Support\Facades\Route;
use SteadfastCollective\StatamicAuth\Mail\PasswordReset;
use SteadfastCollective\StatamicAuth\Http\Controllers\LoginController;
use SteadfastCollective\StatamicAuth\Http\Middleware\UserIsNotLoggedIn;
use SteadfastCollective\StatamicAuth\Http\Controllers\PasswordController;
use SteadfastCollective\StatamicAuth\Http\Controllers\RegisterController;
use SteadfastCollective\StatamicAuth\Http\Controllers\Account\AccountController;
use SteadfastCollective\StatamicAuth\Http\Controllers\Account\DetailsController;
use SteadfastCollective\StatamicAuth\Http\Controllers\Socialite\GithubController;
use SteadfastCollective\StatamicAuth\Http\Controllers\Socialite\GoogleController;
use SteadfastCollective\StatamicAuth\Http\Controllers\Account\TwoFactorController;
use SteadfastCollective\StatamicAuth\Http\Middleware\RequireTwoFactorVerification;
use SteadfastCollective\StatamicAuth\Http\Controllers\Socialite\FacebookController;
use SteadfastCollective\StatamicAuth\Http\Controllers\TwoFactorChallengeController;
use SteadfastCollective\StatamicAuth\Http\Controllers\Account\PasswordController as AccountPasswordController;

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
        if(config('services.google.client_id')) {
            Route::group([
                'prefix' => 'google',
                'as' => 'google.'
            ], function() {
                Route::get('redirect', [GoogleController::class, 'redirect'])->name('redirect');
                Route::get('callback', [GoogleController::class, 'callback'])->name('callback');
            });
        }
        
        // Socialite - Github
        if(config('services.github.client_id')) {
            Route::group([
                'prefix' => 'github',
                'as' => 'github.'
            ], function() {
                Route::get('redirect', [GithubController::class, 'redirect'])->name('redirect');
                Route::get('callback', [GithubController::class, 'callback'])->name('callback');
            });
        }

        // Socialite - Facebook
        if(config('services.facebook.client_id')) {
            Route::group([
                'prefix' => 'facebook',
                'as' => 'facebook.'
            ], function() {
                Route::get('redirect', [FacebookController::class, 'redirect'])->name('redirect');
                Route::get('callback', [FacebookController::class, 'callback'])->name('callback');
            });
        }
    });
    
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    if(config('statamic-auth.account.enabled', true)) {
        Route::group([
            'as' => 'account.',
            'prefix' => config('statamic-auth.account.prefix', 'my-account'),
            'middleware' => [
                'auth',
                RequireTwoFactorVerification::class
            ]
        ], function() {
            Route::get('/', AccountController::class)->name('index');
            Route::patch('update-details', [DetailsController::class, 'update'])->name('details.update');
            Route::patch('update-password', [AccountPasswordController::class, 'update'])->name('password.update');
            
            if(config('statamic-auth.two_factor.enabled', true)) {
                Route::group([
                    'prefix' => '2fa',
                    'as' => '2fa.'
                ], function() {
                    Route::post('enable', [TwoFactorController::class, 'enable'])->name('enable');
                    Route::get('confirm', [TwoFactorController::class, 'showConfirmation'])->name('confirm.show');
                    Route::post('confirm', [TwoFactorController::class, 'storeConfirmation'])->name('confirm.store');
                    Route::post('deactivate', [TwoFactorController::class, 'destroy'])->name('destroy');
                    Route::post('generate-codes', [TwoFactorController::class, 'generateCodes'])->name('generate-codes');
                });
            }
        });
    }

    if(config('statamic-auth.two_factor.enabled', true)) {
        Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('2fa.challenge');
        Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'verify'])->name('2fa.challenge.verify');
    }
});


// Route::group([
//     'prefix' => 'mail'
// ], function() {
//     Route::get('password-reset', function(){
//         return new PasswordReset('https://example.com', \App\Models\User::first());
//     });
// });