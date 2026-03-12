<?php

namespace KwtSMS\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;

class SmsSent
{
    use Dispatchable;

    public function __construct(
        public readonly string $recipient,
        public readonly string $message,
        public readonly string $eventType,
        public readonly ?string $messageId,
    ) {}
}
