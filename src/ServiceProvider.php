<?php

namespace SteadfastCollective\StatamicAuth;

use Statamic\Statamic;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->initRoutes();

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'statamic-auth');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-auth');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/statamic-auth'),
        ], 'statamic-auth-views');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'statamic-auth-migrations');

        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--provider' => 'PragmaRX\Google2FALaravel\ServiceProvider'
            ]);
        });
    
    }
    protected function initRoutes()
    {
        $publishedRoutesPath = base_path('routes/auth.php');
        
        if (file_exists($publishedRoutesPath)) {
            $this->loadRoutesFrom($publishedRoutesPath);
        } else {
            $this->loadRoutesFrom(__DIR__.'/../routes/auth.php');
        }

        $this->publishes([
            __DIR__.'/../routes/auth.php' => $publishedRoutesPath
        ], 'statamic-auth-routes');
    }
}
