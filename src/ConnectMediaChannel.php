<?php

declare(strict_types=1);

namespace ConnectMedia\Laravel;

use Illuminate\Notifications\Notification;

/**
 * Notification channel: add 'connectmedia' (or ConnectMediaChannel::class) to via().
 *
 * The phone number comes from routeNotificationForConnectmedia() on the notifiable,
 * falling back to a phone_number or phone attribute.
 */
class ConnectMediaChannel
{
    public function __construct(private ConnectMedia $sms)
    {
    }

    /** @return array<string, mixed>|null */
    public function send(mixed $notifiable, Notification $notification): ?array
    {
        if (!method_exists($notification, 'toConnectMedia')) {
            throw new \BadMethodCallException(get_class($notification) . ' must define toConnectMedia($notifiable).');
        }

        $message = $notification->toConnectMedia($notifiable);
        if (is_string($message)) {
            $message = new ConnectMediaMessage($message);
        }
        if (!$message instanceof ConnectMediaMessage) {
            throw new \UnexpectedValueException('toConnectMedia() must return a string or a ConnectMediaMessage.');
        }

        $to = $message->to ?? $this->routeFor($notifiable, $notification);
        if ($to === null || $to === '' || $to === []) {
            return null;
        }

        return $this->sms->send($to, $message->content, $message->options());
    }

    /** @return string|string[]|null */
    private function routeFor(mixed $notifiable, Notification $notification): string|array|null
    {
        if (is_object($notifiable) && method_exists($notifiable, 'routeNotificationFor')) {
            $route = $notifiable->routeNotificationFor('connectmedia', $notification)
                ?? $notifiable->routeNotificationFor(self::class, $notification);
            if ($route) {
                return $route;
            }
        }

        foreach (['phone_number', 'phone'] as $attribute) {
            $value = is_object($notifiable) ? ($notifiable->{$attribute} ?? null) : null;
            if ($value) {
                return (string) $value;
            }
        }

        return null;
    }
}
