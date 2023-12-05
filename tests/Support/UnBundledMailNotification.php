<?php

namespace Owowagency\NotificationBundler\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Owowagency\NotificationBundler\BundlesNotifications;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class UnBundledMailNotification extends Notification implements ShouldBundleNotifications, ShouldQueue
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

    public function bundleChannels(): array
    {
        return [];
    }

    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->subject('Bundle')
            ->line("$this->name was unbundled.");
    }

    public function bundleIdentifier(object $notifiable): string
    {
        return "user_$notifiable->id";
    }
}
