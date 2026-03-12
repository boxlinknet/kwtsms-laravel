<?php

namespace KwtSMS\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use KwtSMS\Laravel\KwtSmsManager;

/**
 * @see KwtSmsManager
 */
class KwtSms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'kwtsms';
    }
}
