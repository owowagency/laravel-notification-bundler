<?php

namespace Owowagency\NotificationBundler;

use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\ServiceProvider;
use Owowagency\NotificationBundler\Models\NotificationBundle;

class NotificationBundlerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishables();

        $this->app['events']->listen(JobQueued::class, function (JobQueued $event) {
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
        });

        // TODO Check if models are deleted when job cancelled by other middleware.

        // TODO Check if models are deleted when job failed.
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/notification-bundler.php', 'notification-bundler');
    }

    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/notification-bundler.php' => config_path('notification-bundler.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
