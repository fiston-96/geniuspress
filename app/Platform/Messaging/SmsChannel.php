<?php

namespace App\Platform\Messaging;

use App\Notifications\Notice;
use App\Platform\Models\Customer;
use Illuminate\Notifications\Notification;

/** Canal « SMS » des notifications : seulement pour les clients de la plateforme. */
class SmsChannel
{
    public function __construct(private Sms $sms) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof Customer || ! $notification instanceof Notice) {
            return;
        }
        $text = $notification->sms($notifiable);
        if ($text !== null && $text !== '') {
            $this->sms->toCustomer($notifiable, $text, class_basename($notification));
        }
    }
}
