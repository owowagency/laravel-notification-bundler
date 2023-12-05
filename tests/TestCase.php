<?php

namespace Owowagency\NotificationBundler\Tests;

use Dotgetenv\Dotgetenv;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Owowagency\NotificationBundler\NotificationBundlerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        $this->loadEnvironmentVariables();

        parent::setUp();

        $this->setUpDatabase($this->app);
    }

    protected function loadEnvironmentVariables(): void
    {
        if (! file_exists(__DIR__.'/../.getenv')) {
            return;
        }

        $dotEnv = Dotgetenv::createImmutable(__DIR__.'/..');

        $dotEnv->load();
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        $serviceProviders = [
            NotificationBundlerServiceProvider::class,
        ];

        return $serviceProviders;
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function defineEnvironment($app): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('queue.default', 'database');
        config()->set('queue.connections.database', [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ]);
        config([
            'queue.batching.database' => 'sqlite',
            'queue.failed.database' => 'sqlite',
        ]);

        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * Set up the database.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function setUpDatabase($app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('notifiable_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
        });

        $notificationBundlesTableMigration = require __DIR__.'/../database/migrations/create_notification_bundles_table.php';
        $notificationBundlesTableMigration->up();

        $notificationsTableMigration = require __DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/notifications/0001_01_01_000000_testbench_create_notifications_table.php';
        $notificationsTableMigration->up();

        $jobsTableMigration = require __DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/queue/0001_01_01_000000_testbench_create_jobs_table.php';
        $jobsTableMigration->up();

        $failedJobsTableMigration = require __DIR__.'/../vendor/orchestra/testbench-core/laravel/migrations/2019_08_19_000000_testbench_create_failed_jobs_table.php';
        $failedJobsTableMigration->up();
    }

    public function getTestsPath(string $suffix = ''): string
    {
        if ($suffix !== '') {
            $suffix = "/{$suffix}";
        }

        return __DIR__.$suffix;
    }
}
