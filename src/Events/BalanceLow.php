<?php

namespace KwtSMS\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;

class BalanceLow
{
    use Dispatchable;

    public function __construct(
        public readonly float $currentBalance,
        public readonly float $threshold,
    ) {}
}
