# kwtSMS for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kwtsms/laravel-kwtsms.svg?style=flat-square)](https://packagist.org/packages/kwtsms/laravel-kwtsms)
[![Total Downloads](https://img.shields.io/packagist/dt/kwtsms/laravel-kwtsms.svg?style=flat-square)](https://packagist.org/packages/kwtsms/laravel-kwtsms)
[![License](https://img.shields.io/packagist/l/kwtsms/laravel-kwtsms.svg?style=flat-square)](https://packagist.org/packages/kwtsms/laravel-kwtsms)

A Laravel notification channel package for the [kwtSMS](https://www.kwtsms.com) SMS gateway (Kuwait). Send SMS messages through kwtSMS in any Laravel 10/11/12 application.

## Features

- Laravel Notification Channel integration (plugs into standard Laravel notifications)
- Admin panel: Dashboard, Settings, Templates, Integrations, Logs, Help
- OTP / login verification and password reset SMS support
- Multilingual templates: English and Arabic (RTL ready)
- Phone number normalization (strips +, 00, spaces, Arabic/Hindi digits)
- Message cleaning (strips emojis and hidden characters before send)
- Bulk send with batching (200 numbers per request, 0.2s delay between batches)
- Balance check before send (skips API if cached balance is zero)
- Coverage-aware sending (skips numbers from inactive country prefixes)
- Full SMS log stored locally in database with clear/purge option
- Scheduled daily sync for balance, sender IDs, and coverage
- Test mode support (test=1: queued without real delivery, credits recoverable)
- Rate limiting for OTP and bulk SMS sends
- Global on/off switch for all SMS sending

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12

## Installation

```bash
composer require kwtsms/laravel-kwtsms
```

Publish configuration and run migrations:

```bash
php artisan vendor:publish --tag=kwtsms-config
php artisan vendor:publish --tag=kwtsms-migrations
php artisan migrate
```

## Configuration

Add your kwtSMS API credentials to `.env`:

```env
KWTSMS_USERNAME=your_api_username
KWTSMS_PASSWORD=your_api_password
KWTSMS_SENDER=YOUR-SENDERID
KWTSMS_TEST_MODE=false
KWTSMS_ENABLED=true
```

> **Note:** Use your API username, not your mobile number. Find it in your kwtSMS account API settings page.

## Quick Usage

```php
use KwtSMS\Laravel\Notifications\KwtSmsMessage;
use KwtSMS\Laravel\Channels\KwtSmsChannel;

class OrderShipped extends Notification
{
    public function via($notifiable): array
    {
        return [KwtSmsChannel::class];
    }

    public function toKwtSms($notifiable): KwtSmsMessage
    {
        return KwtSmsMessage::create()
            ->content("Your order has been shipped.");
    }
}
```

Your notifiable model must implement `routeNotificationForKwtSms()`:

```php
public function routeNotificationForKwtSms(): string
{
    return $this->phone; // e.g. "96598765432"
}
```

## Admin Panel

After installation, visit `/kwtsms` in your browser to access the admin panel.

## Security

Report security vulnerabilities to **support@kwtsms.com**. See [SECURITY.md](SECURITY.md) for details.

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.

## About kwtSMS

[kwtSMS](https://www.kwtsms.com) is a Kuwait-based SMS gateway providing reliable A2P messaging for Kuwait (Zain, Ooredoo, STC, Virgin) and international destinations.
