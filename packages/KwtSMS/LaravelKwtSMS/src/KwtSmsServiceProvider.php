<?php

namespace KwtSMS\Laravel;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use KwtSMS\Laravel\Services\BalanceService;
use KwtSMS\Laravel\Services\PhoneNormalizer;
use KwtSMS\Laravel\Services\SmsSender;

class KwtSmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/kwtsms.php',
            'kwtsms'
        );

        $this->app->singleton(PhoneNormalizer::class, fn () => new PhoneNormalizer);
        $this->app->singleton(BalanceService::class, fn () => new BalanceService);
        $this->app->singleton(SmsSender::class, fn (Application $app) => new SmsSender(
            $app->make(PhoneNormalizer::class),
            $app->make(BalanceService::class),
        ));
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
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        if (is_dir(__DIR__.'/../resources/views')) {
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'kwtsms');
        }

        if (is_dir(__DIR__.'/../resources/lang')) {
            $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'kwtsms');
        }

        if (file_exists(__DIR__.'/../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }
    }
}
