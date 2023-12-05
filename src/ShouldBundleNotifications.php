<?php

namespace Owowagency\NotificationBundler;

use Illuminate\Support\Collection;

interface ShouldBundleNotifications
{
    /**
     * Returns an identifier by which the bundle can be identified.
     */
    public function bundleIdentifier(object $notifiable): string;

    /**
     * Get the bundle that should be sent.
     */
    public function getBundle(): Collection;

    /**
     * Sets the bundle on the notification.
     */
    public function setBundle(Collection $bundle): void;

    /**
     * The delay after which the notifications should be bundled and sent.
     */
    public function bundleDelay(object $notifiable): int|\DateTimeInterface;
}
