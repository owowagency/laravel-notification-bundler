<?php

namespace Owowagency\NotificationBundler\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Owowagency\NotificationBundler\BundlesNotifications;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class BundledMultiChannelNotification extends Notification implements ShouldBundleNotifications, ShouldQueue
{
    use BundlesNotifications, Queueable;

    public function __construct(public string $name)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMailBundle(object $notifiable, Collection $notifications)
    {
        $message = (new MailMessage)
            ->subject('Bundle');

        foreach ($notifications as $notification) {
            $message->line("$notification->name was bundled.");
        }

        return $message;
    }

    public function toDatabase(object $notifiable)
    {
        $notifications = $this->getBundle();
        return ['names' => $notifications->pluck('name')->toArray()];
    }

    public function bundleIdentifier(object $notifiable): string
    {
        return "user_$notifiable->id";
    }
}
