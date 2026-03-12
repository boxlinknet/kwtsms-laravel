<?php

namespace KwtSMS\Laravel\Contracts;

interface KwtSmsNotifiable
{
    /**
     * The phone number that should receive the SMS.
     */
    public function getKwtSmsRecipient(): string;

    /**
     * The event type key (e.g. 'order_placed', 'order_shipped').
     * Must match the event_type column in a KwtSmsTemplate record.
     */
    public function getKwtSmsEventType(): string;

    /**
     * Placeholder values for the template body.
     * Keys must match {{placeholder}} tokens in the template.
     *
     * @return array<string, string>
     */
    public function getKwtSmsPlaceholders(): array;
}
