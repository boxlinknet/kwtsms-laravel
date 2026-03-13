<?php

namespace KwtSMS\Laravel\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use KwtSMS\KwtSMS;
use KwtSMS\Laravel\Models\KwtSmsLog;
use KwtSMS\Laravel\Models\KwtSmsSetting;

/**
 * Core SMS sending service.
 *
 * Thin Laravel wrapper around the kwtsms/kwtsms library.
 * Adds pre-send checks, coverage filtering, DB logging, and balance caching.
 *
 * The official library handles: phone normalization, message cleaning,
 * batching (>200 numbers), and ERR013 queue-full retry with backoff.
 *
 * Pre-send checks (in order):
 *   1. Global enabled switch
 *   2. Credentials configured
 *   3. Cached balance > 0
 *   4. Coverage filter (numbers not in coverage are skipped)
 */
class SmsSender
{
    private KwtSMS $client;

    public function __construct(
        private readonly PhoneNormalizer $normalizer,
        private readonly BalanceService $balanceService,
    ) {
        $this->client = new KwtSMS(
            username: config('kwtsms.username', ''),
            password: config('kwtsms.password', ''),
            sender_id: config('kwtsms.sender', 'KWT-SMS'),
            test_mode: (bool) config('kwtsms.test_mode', false),
            log_file: '',
        );
    }

    /**
     * Send an SMS message to one or more recipients.
     *
     * @param  string|string[]  $recipients  Phone number(s). Will be normalized internally.
     * @param  string  $message  Message text. Will be cleaned internally by the library.
     * @param  string|null  $sender  Override sender ID. Defaults to config kwtsms.sender.
     * @param  array<string, mixed>  $options  Optional metadata. Keys: 'event_type' (string).
     * @return array{success: bool, reason?: string, message_id?: string, numbers_sent?: int, points_charged?: float, balance_after?: float}
     *                                                                                                                                       reason values on failure: 'disabled', 'rate_limited', 'not_configured', 'no_balance', 'no_valid_recipients', 'api_error', or an ERR code
     */
    public function send(string|array $recipients, string $message, ?string $sender = null, array $options = []): array
    {
        // 1. Global enabled check
        if (! config('kwtsms.enabled', true)) {
            if (config('kwtsms.debug_logging', false)) {
                Log::debug('KwtSMS: send skipped - SMS disabled by global switch');
            }

            return ['success' => false, 'reason' => 'disabled'];
        }

        // 2. Rate limiting
        $rateLimited = $this->checkRateLimits((array) $recipients);
        if ($rateLimited !== null) {
            return $rateLimited;
        }

        // 3. Credentials configured check
        if (empty(config('kwtsms.username')) || empty(config('kwtsms.password'))) {
            Log::warning('KwtSMS: send skipped - missing API credentials');

            return ['success' => false, 'reason' => 'not_configured'];
        }

        // 4. Balance check
        $balance = $this->balanceService->getCached();
        if ($balance !== null && $balance <= 0) {
            Log::warning('KwtSMS: send blocked - balance is zero');
            $this->logToDatabase(
                recipients: (array) $recipients,
                message: $message,
                sender: $sender ?? config('kwtsms.sender', 'KWT-SMS'),
                apiRequest: [],
                apiResponse: null,
                status: 'failed',
                errorCode: 'ERR010',
                errorMessage: 'Insufficient balance',
                eventType: $options['event_type'] ?? null,
            );

            return ['success' => false, 'reason' => 'no_balance'];
        }

        // 5. Coverage filter
        $coverage = KwtSmsSetting::get('coverage', []);
        $recipientList = (array) $recipients;

        if (! empty($coverage)) {
            $filtered = $this->normalizer->normalizeMany($recipientList, is_array($coverage) ? $coverage : []);
            $recipientList = $filtered['valid'];

            if (empty($recipientList)) {
                if (config('kwtsms.debug_logging', false)) {
                    Log::debug('KwtSMS: send skipped - no recipients in coverage', [
                        'skipped' => $filtered['invalid'],
                    ]);
                }

                return ['success' => false, 'reason' => 'no_valid_recipients'];
            }
        }

        // Guard against empty recipient list (e.g. caller passed an empty array)
        if (empty($recipientList)) {
            return ['success' => false, 'reason' => 'no_valid_recipients'];
        }

        // 6. Send via official library (handles normalization, cleaning, batching internally)
        $effectiveSender = $sender ?? config('kwtsms.sender', 'KWT-SMS');
        $response = $this->client->send($recipientList, $message, $effectiveSender);

        // Build sanitized api_request for logging (no credentials)
        $apiRequest = [
            'mobile' => implode(',', (array) $recipientList),
            'message' => $message,
            'sender' => $effectiveSender,
            'test' => config('kwtsms.test_mode', false) ? 1 : 0,
        ];

        $success = isset($response['result']) && $response['result'] === 'OK';
        $isPartial = isset($response['result']) && $response['result'] === 'PARTIAL';

        // 6. Update balance cache
        if (isset($response['balance-after'])) {
            $this->balanceService->updateCache((float) $response['balance-after']);
        }

        // 7. Log to database
        $sentAt = null;
        if (isset($response['unix-timestamp'])) {
            // API timestamp is GMT+3 (Asia/Kuwait), convert to UTC
            $sentAt = Carbon::createFromTimestamp((int) $response['unix-timestamp'], 'Asia/Kuwait')->utc();
        }

        $this->logToDatabase(
            recipients: (array) $recipientList,
            message: $message,
            sender: $effectiveSender,
            apiRequest: $apiRequest,
            apiResponse: $response,
            status: $success || $isPartial ? 'sent' : 'failed',
            errorCode: $success || $isPartial ? null : ($response['code'] ?? null),
            errorMessage: $success || $isPartial ? null : ($response['description'] ?? null),
            eventType: $options['event_type'] ?? null,
            messageId: $response['msg-id'] ?? (isset($response['msg-ids']) ? implode(',', $response['msg-ids']) : null),
            numbersSent: (int) ($response['numbers'] ?? count($recipientList)),
            pointsCharged: (float) ($response['points-charged'] ?? 0),
            balanceAfter: isset($response['balance-after']) ? (float) $response['balance-after'] : null,
            sentAt: $sentAt,
        );

        if ($success || $isPartial) {
            return [
                'success' => true,
                'message_id' => $response['msg-id'] ?? null,
                'numbers_sent' => (int) ($response['numbers'] ?? count($recipientList)),
                'points_charged' => (float) ($response['points-charged'] ?? 0),
                'balance_after' => isset($response['balance-after']) ? (float) $response['balance-after'] : null,
            ];
        }

        Log::warning('KwtSMS: send failed', [
            'code' => $response['code'] ?? 'UNKNOWN',
            'description' => $response['description'] ?? '',
        ]);

        return [
            'success' => false,
            'reason' => $response['code'] ?? 'api_error',
            'error_description' => $response['description'] ?? '',
        ];
    }

    /**
     * Check per-IP and per-phone rate limits before sending.
     *
     * Both limits apply to single-recipient sends only (OTP, password reset).
     * Bulk sends (order notifications, campaigns) are not rate-limited here
     * because they originate from internal business logic, not user-initiated requests.
     *
     * Per-IP: applies in HTTP context only (skipped in CLI/queues).
     * Per-phone: applies unconditionally for single-recipient sends.
     *
     * @param  string[]  $recipients
     * @return array{success: bool, reason: string}|null Non-null means blocked.
     */
    private function checkRateLimits(array $recipients): ?array
    {
        // Both limits apply to single-recipient sends only (OTP, password reset flows)
        if (count($recipients) !== 1) {
            return null;
        }

        // Per-IP limit - HTTP context only
        if (! app()->runningInConsole()) {
            $ip = request()->ip();
            $ipKey = 'kwtsms:ip:'.$ip;
            $ipLimit = (int) KwtSmsSetting::get('rate_limit_per_ip', config('kwtsms.rate_limit.per_ip_per_hour', 10));

            if (RateLimiter::tooManyAttempts($ipKey, $ipLimit)) {
                Log::warning('KwtSMS: send blocked - IP rate limit exceeded', ['ip' => $ip]);

                return ['success' => false, 'reason' => 'rate_limited'];
            }

            RateLimiter::hit($ipKey, 3600);
        }

        // Per-phone limit
        $phone = $this->normalizer->normalize((string) $recipients[0]);
        $phoneKey = 'kwtsms:phone:'.$phone;
        $phoneLimit = (int) KwtSmsSetting::get('rate_limit_per_phone', config('kwtsms.rate_limit.per_phone_per_hour', 5));

        if (RateLimiter::tooManyAttempts($phoneKey, $phoneLimit)) {
            Log::warning('KwtSMS: send blocked - per-phone rate limit exceeded', [
                'phone_suffix' => substr($phone, -4),
            ]);

            return ['success' => false, 'reason' => 'rate_limited'];
        }

        RateLimiter::hit($phoneKey, 3600);

        return null;
    }

    /**
     * Write an SMS attempt record to the kwtsms_logs table.
     *
     * @param  string[]  $recipients
     * @param  array<string, mixed>  $apiRequest
     * @param  array<string, mixed>|null  $apiResponse
     */
    private function logToDatabase(
        array $recipients,
        string $message,
        string $sender,
        array $apiRequest,
        ?array $apiResponse,
        string $status,
        ?string $errorCode,
        ?string $errorMessage,
        ?string $eventType,
        ?string $messageId = null,
        int $numbersSent = 0,
        float $pointsCharged = 0.0,
        ?float $balanceAfter = null,
        ?Carbon $sentAt = null,
    ): void {
        try {
            KwtSmsLog::create([
                'message_id' => $messageId,
                'recipient' => implode(',', $recipients),
                'sender_id' => $sender,
                'message' => $message,
                'status' => $status,
                'event_type' => $eventType,
                'is_test' => (bool) config('kwtsms.test_mode', false),
                'numbers_sent' => $numbersSent,
                'points_charged' => $pointsCharged,
                'balance_after' => $balanceAfter,
                'api_request' => empty($apiRequest) ? null : $apiRequest,
                'api_response' => $apiResponse,
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'sent_at' => $sentAt,
            ]);
        } catch (\Throwable $e) {
            // Logging must never crash the main send flow
            Log::error('KwtSMS: failed to write SMS log', ['error' => $e->getMessage()]);
        }
    }
}
