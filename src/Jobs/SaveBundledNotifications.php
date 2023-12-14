<?php

namespace Owowagency\NotificationBundler\Jobs;

use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\Events\JobQueued;
use Owowagency\NotificationBundler\Models\NotificationBundle;
use Owowagency\NotificationBundler\ShouldBundleNotifications;

class SaveBundledNotifications
{
    /**
     * Handle the JobQueued event.
     */
    public function handle(JobQueued $event): void
    {
        if (! $event->job instanceof SendQueuedNotifications
            || ! $event->job->notification instanceof ShouldBundleNotifications) {
            return;
        }

        $notifiable = $event->job->notifiables->first();
        /** @var ShouldBundleNotifications $notification */
        $notification = $event->job->notification;
        $channel = $event->job->channels[0];

        if (method_exists($notification, 'bundleChannels')) {
            if (! in_array($channel, $notification->bundleChannels())) {
                return;
            }
        }

        NotificationBundle::create([
            'uuid' => $event->job->notification->id,
            'channel' => $event->job->channels[0],
            'bundle_identifier' => $notification->bundleIdentifier($notifiable),
            'payload' => serialize($notification),
        ]);
    }
}
