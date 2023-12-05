<?php

namespace Owowagency\NotificationBundler\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Owowagency\NotificationBundler\BundlesNotifications;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class OtherBundledMailNotification extends Notification implements ShouldBundleNotifications, ShouldQueue
{
    use BundlesNotifications, Queueable;

    public function __construct(public string $name)
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
            ->subject('Other Bundle');

        foreach ($notifications as $notification) {
            $prefix = $notification instanceof BundledMailNotification ? 'Original ' : 'Other ';
            $message->line("$prefix$notification->name was bundled.");
        }

        return $message;
    }

    public function bundleIdentifier(object $notifiable): string
    {
        return "user_$notifiable->id";
    }
}
