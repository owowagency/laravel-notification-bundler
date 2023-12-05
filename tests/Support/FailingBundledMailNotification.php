<?php

namespace Owowagency\NotificationBundler\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Owowagency\NotificationBundler\BundlesNotifications;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class FailingBundledMailNotification extends Notification implements ShouldBundleNotifications, ShouldQueue
{
    use BundlesNotifications, Queueable;

    public function __construct(public int $tries)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMailBundle(object $notifiable, Collection $notifications)
    {
        throw new \Exception('Oh no! I\'m broken.');
    }

    public function bundleIdentifier(object $notifiable): string
    {
        return "fail_$notifiable->id";
    }
}
