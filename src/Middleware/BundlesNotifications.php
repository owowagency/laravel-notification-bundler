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
        $identifier = $notification->bundleIdentifier($this->notifiable);

        $notificationBundle = NotificationBundle::query()
            ->where('bundle_identifier', $identifier)
            ->latest()
            ->get();

        if ($notificationBundle->first()->uuid === $notification->id) {
            $bundle = $notificationBundle->pluck('unserialized_payload');
            $notification->setBundle($bundle);

            NotificationBundle::query()
                ->whereIn('uuid', $notificationBundle->pluck('uuid'))
                ->delete();

            return $next($job);
        }

        return false;
    }
}
