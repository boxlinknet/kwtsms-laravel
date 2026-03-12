<?php

namespace KwtSMS\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

class KwtSms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'kwtsms';
    }
}
