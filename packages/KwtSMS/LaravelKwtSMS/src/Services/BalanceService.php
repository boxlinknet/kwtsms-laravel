<?php

namespace KwtSMS\Laravel\Services;

use KwtSMS\Laravel\Models\KwtSmsSetting;

/**
 * Balance caching and sync service.
 *
 * Caches the kwtSMS account balance in the kwtsms_settings table.
 * After every send(), SmsSender calls updateCache() with the balance-after
 * value from the API response, so we avoid redundant API calls.
 *
 * Settings keys:
 *   - 'balance_available': float, last known available balance
 *   - 'balance_synced_at': ISO datetime string of last sync
 */
class BalanceService
{
    private const BALANCE_KEY = 'balance_available';

    private const SYNCED_AT_KEY = 'balance_synced_at';

    /**
     * Get the cached balance from the database.
     *
     * Returns null if no balance has been synced yet.
     */
    public function getCached(): ?float
    {
        $value = KwtSmsSetting::get(self::BALANCE_KEY);

        if ($value === null) {
            return null;
        }

        return (float) $value;
    }

    /**
     * Update the cached balance (called after every successful send).
     */
    public function updateCache(float $balance): void
    {
        KwtSmsSetting::set(self::BALANCE_KEY, $balance);
        KwtSmsSetting::set(self::SYNCED_AT_KEY, now()->toIso8601String());
    }

    /**
     * Sync balance from the kwtSMS API and update the cache.
     *
     * Calls /API/balance/ directly. The kwtsms/kwtsms library balance() method
     * reads the wrong field ('balance' instead of 'available'), so we call
     * the API ourselves to get the correct value.
     *
     * @return array{available?: float, purchased?: float, error?: string}
     */
    public function syncFromApi(): array
    {
        $response = $this->fetchBalanceFromApi();

        if (! isset($response['result']) || $response['result'] !== 'OK') {
            return ['error' => $response['description'] ?? 'Failed to retrieve balance from API'];
        }

        $available = (float) ($response['available'] ?? 0);
        $purchased = (float) ($response['purchased'] ?? 0);

        $this->updateCache($available);

        return ['available' => $available, 'purchased' => $purchased];
    }

    /**
     * Fetch balance from the kwtSMS /API/balance/ endpoint.
     *
     * @return array<string, mixed>
     */
    private function fetchBalanceFromApi(): array
    {
        $url = rtrim((string) config('kwtsms.api_base_url', 'https://www.kwtsms.com/API/'), '/').'/balance/';
        $payload = json_encode([
            'username' => config('kwtsms.username', ''),
            'password' => config('kwtsms.password', ''),
        ]);

        if ($payload === false) {
            return ['result' => 'ERROR', 'description' => 'Failed to encode request'];
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return ['result' => 'ERROR', 'description' => 'Failed to initialize HTTP request'];
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) config('kwtsms.timeout', 30));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

        $body = curl_exec($ch);
        curl_close($ch);

        if ($body === false || $body === '') {
            return ['result' => 'ERROR', 'description' => 'Empty response from balance API'];
        }

        $decoded = json_decode((string) $body, true);

        return is_array($decoded) ? $decoded : ['result' => 'ERROR', 'description' => 'Invalid JSON from balance API'];
    }
}
