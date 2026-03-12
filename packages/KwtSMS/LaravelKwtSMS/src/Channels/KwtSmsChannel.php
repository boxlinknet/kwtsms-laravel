<?php

namespace KwtSMS\Laravel\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use KwtSMS\Laravel\Notifications\KwtSmsMessage;
use KwtSMS\Laravel\Services\SmsSender;

/**
 * Laravel Notification Channel for kwtSMS.
 *
 * Register this channel in your notification's via() method:
 *
 *   public function via(mixed $notifiable): array
 *   {
 *       return [KwtSmsChannel::class];
 *   }
 *
 * The notifiable model must implement routeNotificationForKwtSms()
 * returning the recipient's phone number.
 *
 * Example:
 *
 *   public function routeNotificationForKwtSms(Notification $notification): string
 *   {
 *       return $this->phone;
 *   }
 */
class KwtSmsChannel
{
    public function __construct(private readonly SmsSender $sender) {}

    /**
     * Send the given notification via kwtSMS.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toKwtSms')) {
            return;
        }

        $message = $notification->toKwtSms($notifiable);

        if (! $message instanceof KwtSmsMessage) {
            return;
        }

        if (empty($message->getContent())) {
            Log::warning('KwtSmsChannel: message content is empty, skipping send', [
                'notifiable' => get_class($notifiable),
                'notification' => get_class($notification),
            ]);

            return;
        }

        $phone = $notifiable->routeNotificationFor('KwtSms', $notification);

        if (empty($phone)) {
            Log::warning('KwtSmsChannel: notifiable has no phone for KwtSms routing', [
                'notifiable' => get_class($notifiable),
            ]);

            return;
        }

        $this->sender->send(
            [$phone],
            $message->getContent(),
            $message->getSender(),
            ['event_type' => $message->getEventType()]
        );
    }
}
