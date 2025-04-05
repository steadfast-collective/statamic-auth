<?php

namespace SteadfastCollective\StatamicAuth;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->initRoutes();

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'statamic-auth');

    }

    private function initRoutes()
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
