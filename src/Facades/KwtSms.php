<?php

namespace KwtSMS\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use KwtSMS\Laravel\Services\SmsSender;

/**
 * KwtSms Facade
 *
 * Provides static access to the SmsSender service.
 *
 * Usage:
 *   KwtSms::send('96598765432', 'Your message here');
 *   KwtSms::send(['96598765432', '96512345678'], 'Bulk message');
 *
 * @see SmsSender
 *
 * @method static array send(string|array $recipients, string $message, ?string $sender = null, array $options = [])
 */
class KwtSms extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SmsSender::class;
    }
}
