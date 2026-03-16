<?php

namespace KwtSMS\Laravel\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use KwtSMS\Laravel\Models\KwtSmsSetting;
use KwtSMS\Laravel\Models\KwtSmsTemplate;
use KwtSMS\Laravel\Services\SmsSender;

/**
 * Subscribes to Laravel authentication events and sends admin alert SMS notifications.
 *
 * Registered events: Registered, PasswordReset
 *
 * Each handler checks the 'admin_alerts' setting before sending.
 * Templates are resolved from the kwtsms_templates table by event_type and locale.
 */
class KwtSmsEventSubscriber
{
    public function __construct(private readonly SmsSender $sender) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Registered::class => 'handleUserRegistered',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }

    public function handleUserRegistered(Registered $event): void
    {
        $this->sendAlertIfEnabled('user_registered', 'New user registered.');
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->sendAlertIfEnabled('password_reset', 'A password has been reset.');
    }

    private function sendAlertIfEnabled(string $eventType, string $fallbackMessage): void
    {
        $alerts = KwtSmsSetting::get('admin_alerts', []);

        if (empty($alerts[$eventType])) {
            return;
        }

        $phone = (string) KwtSmsSetting::get('admin_phone', '');

        if ($phone === '') {
            return;
        }

        $message = $this->resolveMessage($eventType, $fallbackMessage);

        try {
            $this->sender->send($phone, $message, null, [
                'event_type' => $eventType,
                'recipient_type' => 'admin',
            ]);
        } catch (\Throwable $e) {
            Log::error('KwtSMS: event subscriber failed to send', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveMessage(string $eventType, string $fallback): string
    {
        $locale = app()->getLocale();

        $template = KwtSmsTemplate::query()
            ->forEvent($eventType, $locale)
            ->active()
            ->first();

        if ($template === null && $locale !== 'en') {
            $template = KwtSmsTemplate::query()
                ->forEvent($eventType, 'en')
                ->active()
                ->first();
        }

        if ($template === null) {
            return $fallback;
        }

        return $template->render([]);
    }
}
