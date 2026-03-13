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

    /**
     * Returns the recipient phone with all but the last 4 digits masked.
     * Use this in logs, events, and notifications to avoid exposing full phone numbers.
     */
    public function recipientMasked(): string
    {
        $len = strlen($this->recipient);

        return $len > 4 ? str_repeat('*', $len - 4).substr($this->recipient, -4) : $this->recipient;
    }
}
