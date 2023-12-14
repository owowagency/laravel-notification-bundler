<?php

namespace Owowagency\NotificationBundler;

use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\ServiceProvider;
use Owowagency\NotificationBundler\Jobs\SaveBundledNotifications;

class NotificationBundlerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishables();

        $this->app['events']->listen(JobQueued::class, SaveBundledNotifications::class);
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
