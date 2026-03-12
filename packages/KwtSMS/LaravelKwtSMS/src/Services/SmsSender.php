<?php

namespace KwtSMS\Laravel\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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

        // 2. Credentials configured check
        if (empty(config('kwtsms.username')) || empty(config('kwtsms.password'))) {
            Log::warning('KwtSMS: send skipped - missing API credentials');

            return ['success' => false, 'reason' => 'not_configured'];
        }

        // 3. Balance check
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

        // 4. Coverage filter
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

        // 5. Send via official library (handles normalization, cleaning, batching internally)
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
