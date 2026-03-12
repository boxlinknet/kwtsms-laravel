<?php

namespace KwtSMS\Laravel\Services;

use KwtSMS\KwtSMS;
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
     * Returns array with 'available' key on success, or 'error' key on failure.
     *
     * @return array{available?: float, error?: string}
     */
    public function syncFromApi(): array
    {
        $client = $this->makeClient();
        $balance = $client->balance();

        if ($balance === null) {
            return ['error' => 'Failed to retrieve balance from API'];
        }

        $this->updateCache($balance);

        return ['available' => $balance];
    }

    /**
     * Create a configured KwtSMS client instance.
     */
    private function makeClient(): KwtSMS
    {
        return new KwtSMS(
            username: config('kwtsms.username', ''),
            password: config('kwtsms.password', ''),
            sender_id: config('kwtsms.sender', 'KWT-SMS'),
            test_mode: (bool) config('kwtsms.test_mode', false),
            log_file: '',
        );
    }
}
