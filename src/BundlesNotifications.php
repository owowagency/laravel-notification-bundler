<?php

namespace Owowagency\NotificationBundler;

use Illuminate\Support\Collection;
use Owowagency\NotificationBundler\Middleware\BundlesNotifications as BundlesNotificationsMiddleware;

trait BundlesNotifications
{
    private Collection $bundle;

    /**
     * {@inheritdoc}
     */
    public function getBundle(): Collection
    {
        return $this->bundle;
    }

    /**
     * {@inheritdoc}
     */
    public function setBundle(Collection $bundle): void
    {
        $this->bundle = $bundle;
    }

    /**
     * Forward to... method calls to the to...Bundle method.
     */
    public function __call($method, $args)
    {
        if (str_starts_with($method, 'to')) {
            return call_user_func_array([$this, "{$method}Bundle"], [...$args, $this->getBundle()]);
        }

        return call_user_func_array([$this, $method], $args);
    }

    /**
     * {@inheritdoc}
     */
    public function bundleDelay(object $notifiable): int|\DateTimeInterface
    {
        return config('notification-bundler.bundle_notifications_after_seconds');
    }

    /**
     * Determine the notification's delivery delay.
     *
     * @return array<string, \Illuminate\Support\Carbon|int>
     */
    public function withDelay(object $notifiable): array
    {
        $delay = $this->bundleDelay($notifiable);

        return collect($this->via($notifiable))
            ->mapWithKeys(function ($channel) use ($delay) {
                return [$channel => $delay];
            })
            ->toArray();
    }

    /**
     * Returns the middleware that should be applied on the job that sends the
     * notification.
     */
    public function middleware(object $notifiable): array
    {
        return [new BundlesNotificationsMiddleware($notifiable)];
    }
}
