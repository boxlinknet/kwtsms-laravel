<?php

namespace KwtSMS\Laravel;

use Illuminate\Support\ServiceProvider;

class KwtSmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/kwtsms.php',
            'kwtsms'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kwtsms.php' => config_path('kwtsms.php'),
            ], 'kwtsms-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'kwtsms-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/kwtsms'),
            ], 'kwtsms-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/kwtsms'),
            ], 'kwtsms-lang');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kwtsms');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'kwtsms');

        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }
}
