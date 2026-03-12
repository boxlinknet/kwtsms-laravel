<?php

namespace KwtSMS\Laravel\Notifications;

use Illuminate\Support\Facades\App;
use KwtSMS\Laravel\Models\KwtSmsTemplate;

/**
 * Fluent SMS message builder for the kwtSMS notification channel.
 *
 * Usage in a notification:
 *
 *   public function toKwtSms(mixed $notifiable): KwtSmsMessage
 *   {
 *       return KwtSmsMessage::create()
 *           ->content("Your OTP is: {$this->code}")
 *           ->eventType('otp');
 *   }
 *
 * Or using a stored template:
 *
 *   return KwtSmsMessage::create()
 *       ->template('otp', ['otp_code' => $this->code, 'app_name' => config('app.name')])
 *       ->eventType('otp');
 */
class KwtSmsMessage
{
    private string $content = '';

    private ?string $sender = null;

    private string $eventType = 'custom';

    /**
     * Create a new message instance.
     */
    public static function create(): static
    {
        return new static;
    }

    /**
     * Set the message body.
     */
    public function content(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Override the sender ID for this message.
     * Defaults to config kwtsms.sender if not set.
     */
    public function sender(string $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Set the event type for logging and integration rules.
     *
     * Common values: otp, password_reset, order_placed, order_status,
     *                order_shipped, order_cancelled, cod_otp, custom
     */
    public function eventType(string $type): static
    {
        $this->eventType = $type;

        return $this;
    }

    /**
     * Load content from a named template stored in kwtsms_templates.
     *
     * Falls back to 'en' locale if the requested locale has no match.
     *
     * @param  array<string, string>  $placeholders  Key-value pairs to replace {key} tokens.
     */
    public function template(string $name, array $placeholders = [], ?string $locale = null): static
    {
        $locale = $locale ?? App::getLocale();

        $template = KwtSmsTemplate::query()
            ->where('name', $name)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();

        // Fallback to 'en' if the requested locale is not available
        if ($template === null && $locale !== 'en') {
            $template = KwtSmsTemplate::query()
                ->where('name', $name)
                ->where('locale', 'en')
                ->where('is_active', true)
                ->first();
        }

        if ($template !== null) {
            $this->content = $this->replacePlaceholders($template->body, $placeholders);
        }

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @param  array<string, string>  $placeholders
     */
    private function replacePlaceholders(string $body, array $placeholders): string
    {
        foreach ($placeholders as $key => $value) {
            $body = str_replace('{'.$key.'}', (string) $value, $body);
        }

        return $body;
    }
}
