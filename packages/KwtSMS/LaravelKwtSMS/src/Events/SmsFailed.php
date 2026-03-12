<?php

namespace KwtSMS\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;

class SmsFailed
{
    use Dispatchable;

    public function __construct(
        public readonly string $recipient,
        public readonly string $message,
        public readonly string $reason,
        public readonly ?string $errorCode,
    ) {}
}
