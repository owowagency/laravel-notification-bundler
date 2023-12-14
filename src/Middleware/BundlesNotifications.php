<?php

namespace Owowagency\NotificationBundler\Middleware;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\SendQueuedNotifications;
use Owowagency\NotificationBundler\Models\NotificationBundle;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class BundlesNotifications
{
    public function __construct(private readonly mixed $notifiable)
    {
        //
    }

    /**
     * Process the queued job.
     */
    public function handle(ShouldQueue $job, \Closure $next)
    {
        if (! $job instanceof SendQueuedNotifications
            || ! $job->notification instanceof ShouldBundleNotifications) {
            return $next($job);
        }

        /** @var ShouldBundleNotifications $notification */
        $notification = $job->notification;
        $channel = $job->channels[0];
        $identifier = $notification->bundleIdentifier($this->notifiable);

        if (method_exists($notification, 'bundleChannels')) {
            if (! in_array($channel, $notification->bundleChannels())) {
                return $next($job);
            }
        }

        $notificationBundleQuery = NotificationBundle::query()
            ->where('channel', $channel)
            ->where('bundle_identifier', $identifier);

        $notificationBundle = $notificationBundleQuery->clone()
            ->latest()
            ->get();

        // The first notification is the latest. If that is the same as the notification we're currently handling,
        // we don't have any other notifications in the queue that should be bundled.
        if ($notificationBundle->first()->uuid === $notification->id) {
            $bundle = $notificationBundle->pluck('unserialized_payload');
            $notification->setBundle($bundle);

            $notificationBundleQuery->delete();

            return $next($job);
        }

        return false;
    }
}
