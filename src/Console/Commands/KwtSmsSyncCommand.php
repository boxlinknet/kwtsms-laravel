<?php

namespace KwtSMS\Laravel\Console\Commands;

use Illuminate\Console\Command;
use KwtSMS\KwtSMS;
use KwtSMS\Laravel\Events\BalanceLow;
use KwtSMS\Laravel\Models\KwtSmsSetting;
use KwtSMS\Laravel\Services\BalanceService;

/**
 * Artisan command: kwtsms:sync
 *
 * Syncs kwtSMS account data (balance, sender IDs, coverage) from the API
 * and caches the results in kwtsms_settings for use by the admin panel
 * and pre-send checks.
 *
 * Scheduled daily at 03:00 via the service provider.
 */
class KwtSmsSyncCommand extends Command
{
    protected $signature = 'kwtsms:sync {--force : Force sync even if recently synced}';

    protected $description = 'Sync kwtSMS account data: balance, sender IDs, and coverage';

    public function __construct(private readonly BalanceService $balanceService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (empty(config('kwtsms.username')) || empty(config('kwtsms.password'))) {
            $this->error('kwtSMS credentials not configured. Set KWTSMS_USERNAME and KWTSMS_PASSWORD in .env');

            return self::FAILURE;
        }

        $client = new KwtSMS(
            username: config('kwtsms.username', ''),
            password: config('kwtsms.password', ''),
            sender_id: config('kwtsms.sender', 'KWT-SMS'),
            test_mode: false,
            log_file: '',
        );

        $this->line('Syncing kwtSMS account data...');

        // 1. Sync balance via BalanceService (reads 'available' field correctly)
        $balanceResult = $this->balanceService->syncFromApi();
        if (isset($balanceResult['error'])) {
            $this->error('Failed to retrieve balance: '.$balanceResult['error']);

            return self::FAILURE;
        }
        $balance = $balanceResult['available'];
        $this->line("Balance: {$balance} credits");

        // 2. Sync sender IDs
        $senderidsResponse = $client->senderids();
        if (isset($senderidsResponse['result']) && $senderidsResponse['result'] === 'OK') {
            $senderids = $senderidsResponse['senderids'] ?? [];
            KwtSmsSetting::set('senderids', $senderids);
            $this->line('Sender IDs: '.implode(', ', $senderids));
        } else {
            $this->warn('Failed to sync sender IDs: '.($senderidsResponse['description'] ?? 'Unknown error'));
        }

        // 3. Sync coverage
        $coverageResponse = $client->coverage();
        if (isset($coverageResponse['result']) && $coverageResponse['result'] === 'OK') {
            $prefixes = $coverageResponse['prefixes'] ?? [];
            KwtSmsSetting::set('coverage', $prefixes);
            $this->line('Coverage prefixes: '.count($prefixes).' active');
            if ($this->getOutput()->isVerbose()) {
                $this->line(implode(', ', $prefixes));
            }
        } else {
            $this->warn('Failed to sync coverage: '.($coverageResponse['description'] ?? 'Unknown error'));
        }

        // 4. Check low balance threshold
        $lowBalanceThreshold = (float) KwtSmsSetting::get('low_balance_threshold', 50);
        if ($balance < $lowBalanceThreshold) {
            $this->warn("Balance ({$balance}) is below low balance threshold ({$lowBalanceThreshold}).");
            event(new BalanceLow($balance, $lowBalanceThreshold));
        }

        KwtSmsSetting::set('last_sync_at', now()->toIso8601String());
        $this->info('kwtSMS sync completed successfully.');

        return self::SUCCESS;
    }
}
