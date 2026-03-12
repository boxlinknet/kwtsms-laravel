# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.1] - 2026-03-13

### Added

- GitHub Actions CI: Laravel Pint code style check (`pint.yml`) on push/PR to main
- GitHub Actions CI: PHP syntax check across 8.1, 8.2, 8.3 (`php.yml`) on push/PR to main

### Changed

- Added `laravel/pint` to `require-dev` in `composer.json`

---

## [1.0.0] - 2026-03-13

### Added

- **kwtSMS REST/JSON API client** (`KwtSMS\KwtSMS`): send, balance, senderids, validate, coverage, status, dlr endpoints
- **Laravel Notification Channel** (`KwtSmsChannel` + `KwtSmsMessage`): plugs into standard `$notifiable->notify()` flow
- **SmsSender service**: core send pipeline with pre-send guards (enabled, configured, balance, coverage)
- **Bulk send with batching**: max 200 numbers per request, 0.2s delay between batches to stay within API rate limits
- **PhoneNormalizer service**: strips `+`, `00`, spaces, dashes, converts Arabic/Hindi digits to Latin
- **MessageUtils (MessageCleaner)**: strips emojis, zero-width characters, HTML tags, converts Arabic numerals
- **BalanceService**: syncs and caches account balance, powers low-balance threshold checks
- **KwtSmsTemplate model**: SMS templates with `{{variable_name}}` placeholder substitution
- **KwtSmsSetting model**: key/value settings store backed by the `kwtsms_settings` table
- **KwtSmsLog model**: per-send log with full API request/response JSON, event type, status, points charged
- **Admin panel** (7 tabs): Dashboard, Settings, Templates, Integrations, Logs, Admin Alerts, Help
  - Dashboard: live balance, send count, failed count, recent logs
  - Settings: credentials, test connection button, low balance threshold, admin phone
  - Templates: create/edit/delete/activate templates with Arabic character counter
  - Integrations: toggle which events trigger SMS sends
  - Logs: view, export CSV, clear all
  - Admin Alerts: configure alert types (low balance, send failure, daily summary, API error, OTP flood)
  - Help: quick start guide and code examples
- **Default SMS templates** (English + Arabic) for: `otp`, `password_reset`, `order_placed`, `order_confirmed`, `order_shipped`, `order_delivered`, `order_cancelled`, `order_status`, `cod_otp`, `low_balance_alert`
- **KwtSmsSyncCommand** (`php artisan kwtsms:sync`): syncs balance, sender IDs, and coverage from API; scheduled daily at 03:00
- **KwtSms Facade** for static access: `KwtSms::send()`, `KwtSms::balance()`, `KwtSms::senderids()`
- **Event system**: `BalanceLow` event, `KwtSmsNotifiable` contract, order/auth event integration hooks
- **Global kill switch**: `KWTSMS_ENABLED=false` blocks all sends without touching other config
- **Test mode**: `KWTSMS_TEST_MODE=true` queues messages without delivery, no credits consumed
- **PHPUnit feature test suite**: 27 tests, 44 assertions, all hitting real API with test mode
- **Published config**: `config/kwtsms.php` with all settings documented
- **Published migrations**: `kwtsms_logs`, `kwtsms_settings`, `kwtsms_templates` tables
