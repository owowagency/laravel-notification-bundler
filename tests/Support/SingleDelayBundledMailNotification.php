<?php

namespace Owowagency\NotificationBundler\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Owowagency\NotificationBundler\BundlesNotifications;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class SingleDelayBundledMailNotification extends Notification implements ShouldBundleNotifications, ShouldQueue
{
    use BundlesNotifications, Queueable;

    public function __construct(public int $bundleDelay)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMailBundle(object $notifiable, Collection $notifications)
    {
        $message = (new MailMessage)
            ->subject('Bundle');

        foreach ($notifications as $notification) {
            $message->line("Delayed notification was bundled.");
        }

        return $message;
    }

    public function bundleDelay(object $notifiable): int|\DateTimeInterface
    {
        return $this->bundleDelay;
    }

    public function bundleIdentifier(object $notifiable): string
    {
        return "user_$notifiable->id";
    }
}
