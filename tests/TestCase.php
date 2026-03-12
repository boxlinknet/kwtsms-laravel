<?php

namespace KwtSMS\Laravel\Tests;

use KwtSMS\Laravel\KwtSmsServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            KwtSmsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('kwtsms.username', env('KWTSMS_USERNAME', ''));
        $app['config']->set('kwtsms.password', env('KWTSMS_PASSWORD', ''));
        $app['config']->set('kwtsms.sender', env('KWTSMS_SENDER', 'KWT-SMS'));
        $app['config']->set('kwtsms.test_mode', true);
        $app['config']->set('kwtsms.enabled', true);
        $app['config']->set('database.default', 'testing');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
