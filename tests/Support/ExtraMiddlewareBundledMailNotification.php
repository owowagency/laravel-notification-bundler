<?php

namespace Owowagency\NotificationBundler\Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Owowagency\NotificationBundler\BundlesNotifications;
use Owowagency\NotificationBundler\ShouldBundleNotifications;
use Owowagency\NotificationBundler\Tests\Support\Middleware\StopExecution;

class ExtraMiddlewareBundledMailNotification extends Notification implements ShouldBundleNotifications, ShouldQueue
{
    use BundlesNotifications {
        middleware as bundledMiddleware;
    }
    use Queueable;

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
            ->subject('Bundle');

        foreach ($notifications as $notification) {
            $message->line("$notification->name was bundled.");
        }

        return $message;
    }

    public function bundleIdentifier(object $notifiable): string
    {
        return "user_$notifiable->id";
    }

    public function middleware(object $notifiable): array
    {
        return [
            ...$this->bundledMiddleware($notifiable),
            StopExecution::class,
        ];
    }
}
