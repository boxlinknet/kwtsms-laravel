# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial package scaffold
- kwtSMS REST/JSON API client
- Laravel Notification Channel integration
- Admin panel with Dashboard, Settings, Templates, Integrations, Logs, Help tabs
- Phone number normalization (international format, strips +/00/spaces/Arabic digits)
- Message cleaning (strips emojis and hidden characters)
- Bulk send with batching (max 200 per request, 0.2s delay)
- Balance check before send
- Coverage-aware sending
- SMS log database storage with clear/purge
- Daily scheduled sync (balance, sender IDs, coverage)
- Test mode support
- Rate limiting for OTP sends
- Multilingual support: English and Arabic
- Order notification integration hooks
