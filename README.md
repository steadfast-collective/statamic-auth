# Statamic Auth
> [!IMPORTANT]
> This addon only supports users stored via the eloquent driver.

Sets up a bunch of public-facing authentication routes, so your users can login/logout etc without ever seeing the Statamic login page.

Out-of-the-box, this addon provides:
- Login routes
- Logout
- Forgot password flow
- Registration flow
- "Sign in with..." logins, using [Laravel Socialite](https://laravel.com/docs/12.x/socialite).
-  Two Factor Authentication

  
  ## Table of Contents
 - [🛠️ Installation](#Installation)
 - [⚙️ Configuration](#Configuration)
 - [👀 Views](#Views)
 - [🍸 Socialite](#socialite)
 - [📱 Two Factor Authentication](#two-factor-authentication)
 - [📅 Events](#events)
  


## Installation
- Add the following to your  `composer.json`:

```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/steadfast-collective/statamic-auth.git" 
    }
]

```
- Then run the following command from your project root:

``` bash

composer  require  steadfast-collective/statamic-auth

```
- Optionally publish the addon's config using `php artisan vendor:publish --tag="statamic-auth-config"`.
   - It's recommended that you do this, but if not, the config sets some sensible defaults.

- Add the `HasCustomAuthFlow` trait to your user model.

- Add the following to your `bootstrap/app.php`:
```
->withMiddleware(function (Middleware $middleware) {
    $middleware->redirectGuestsTo(fn (Request $request) => route('auth.login'));
})
```

## Configuration

This addon was built on top of the Peak starter kit, using appropriate(ish) styling.

`layout`:
If you want the auth routes to use a specific ***antlers*** layout, specify which one in the this configuration option.

`default_roles`:
When a user registers, the roles provided in this array will be applied.

You'll need to use the role handle.

`cp_roles`:
In some cases, you might want to redirect users from the custom auth flow to the Statamic CP. Provide the role handle here to determine which users get redirected.

> [!IMPORTANT]
> Super users are always redirected.

`redirect`:
When the user logs in, where are they redirected to? Use the route's name. Defaults to the `auth.account.index` page.

`register`:
- `enabled`:  Set this to `false` if you don't want the registration routes to be added, in case your users are manually added via the CP, come from a third party etc.
- `prefix`: When enabled, all registration routes are prefixed with `/register`. Changing this value will affect the URL of these routes.

`account`:
- `enabled`: Set this to `false` if you don't want your users updating their own details.
- `prefix`: Same as `register.prefix`.
- `layout`: The antlers layout to use for account-specific pages.
- `users_can_delete_account`: Set to `false`, and the route for deleting an account won't be present.
- `users_can_update_password`: Set to `false`, and the password update routes won't exist.
   - Note; this does not affect the "Forgot Password" flow.
- `two_factor`
    - `enabled`: set to `true` to enable authenticator-app based 2FA. Use in combination with `account.enabled = true`. 

    
## Views
We expect you'll want to change the default views provided by this addon to better fit the site you're integrating this with. 

- Run `php artisan vendor:publish --tag="statamic-auth-views"`
- Then, you can update all of the views however you like. 

## Routes
Don't like how a particular route is implemented? 
`Run php artisan vendor:publish --tag="statamic-auth-routes"`, which will create a `auth.php` file in your routes directory.

Feel free to override and rename the routes however you please.

> [!NOTE]
> If you want to add custom Socialite integrations, you'll need to do this.

## Socialite
Out of the box, this addon has support for Laravel Socialite, and we've added the following drivers:
- Github
- Google
- Facebook

However, you'll probably want to add some others. 

1. Publish the routes file as mentioned [here](#Routes)
2. When creating a new controller, extend the existing `SocialiteController`, so your controller will look something like this:
```
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use SteadfastCollective\StatamicAuth\Http\Controllers\Socialite\SocialiteController;

class newController extends SocialiteController
{
    protected $driver = "driverName";

    public function callback(): RedirectResponse
    {
        $oauthUser = Socialite::driver($this->driver)->user();

        // your implementation

        return  $this->login($user);
    }
}
```
3. Reference your controller in the `auth` routes file, like so:
```
Route::group([
    'prefix' => 'driverName',
    'as' => 'driverName.'
], function() {
    Route::get('redirect', [newController::class, 'redirect'])->name('redirect');
    Route::get('callback', [newController::class, 'callback'])->name('callback');
});
```
The socialite drivers implemented in this addon will pick up the config values in `services`, so for all intents and purposes you can implement them as you would in any other Laravel app. 

> [!NOTE]
> The redirect route is automatically handled by the SocialiteController your class should be extending.

## Two Factor Authentication
To setup:
1. Run `php artisan vendor:publish --tag="statamic-auth-migrations"`
2. Run `php artisan migrate`
3. Ensure `two_factor.enabled` and `account.enabled` are both set to `true` (so the user can manage the setup).
4. Add the `UsesTwoFactor` trait to your user Model:
```
use SteadfastCollective\StatamicAuth\Traits\UsesTwoFactor;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use UsesTwoFactor;
```

There's not really much more to it. Views will be included when running the view publish command referenced in [Views](#views).

## Events
This addon dispatches a number of events, incase you don't want to override the controllers, but want to run additional code when certain actions occur.

- `AccountDetailsUpdated`
    - Run when the user updates their details via the `my-account/update-details` route. 
    - Contains `App\Models\User $user` within `$event`.
- `PasswordReset`
    - Runs when user password is updated via Forgotten password flow. 
    - Contains `App\Models\User $user` within `$event`.
- `PasswordResetLinkSent`
    - Runs when password reset link is sent via forgotten password flow.
    - Contains `$email` within `$event`.
- `UserDeleted`
    - Runs when user deletes their account via the account page.
    - Contains `App\Models\User $user` within `$event`.
- `UserLoggedIn`
    - Runs when user logs in via `auth.login.store` route.
    - Contains `App\Models\User $user` within `$event`.
- `UserLoggedOut`
    - Runs when user logs out via `auth.logout` route.
    - Contains `App\Models\User $user` within `$event`.
- `UserRegistered`
    - Runs when user has successfully registered, but before the user is automatically logged in.
    - Contains `App\Models\User $user` within `$event`.

## Translations
All text used within this addon is located within `lang/en/strings.php`. You can publish this translation file by running `php artisan vendor:publish --tag="statamic-auth-translations"`. 

However, if you're completely overriding the [views](#views), you probably don't need to do this.
