<?php

namespace Owowagency\NotificationBundler\Tests;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

trait RefreshDatabase
{
    /**
     * Indicates if the test database has been migrated.
     */
    protected static bool $migrated = false;

    /**
     * An array of table increments that is captured at the start of running
     * all tests.
     *
     * @param \Illuminate\Support\Collection
     */
    protected static $incrementsStart;

    /**
     * An array of table increments that is captured at the end of each test.
     * Used to determine which tables need to reset the auto increment.
     *
     * @param \Illuminate\Support\Collection
     */
    protected static $incrementsPrev;

    /**
     * Define hooks to migrate the database before and after each test.
     */
    public function refreshDatabase(): void
    {
        // Migrate and seed the database when it did not happen yet.
        if (! static::$migrated) {
            $this->artisan('migrate:fresh --seed');

            // Migrate from tests support.
            $this->artisan('migrate --path=tests/Support/Database/Migrations');

            static::$migrated = true;
        }

        // Capture the increments when it did not happen yet. Otherwise
        // rollback the increments of the captured.
        if (is_null(static::$incrementsStart)) {
            static::$incrementsStart = $this->getIncrements();
        } else {
            $this->rollbackIncrements();
        }

        // Start a database transaction.
        $this->beginDatabaseTransaction();
    }

    /**
     * Handle database transactions on the specified connections.
     */
    public function beginDatabaseTransaction(): void
    {
        $database = $this->app->make('db');

        // Start transaction.
        $database->connection()->beginTransaction();

        // Rollback transaction before application is being destroyed.
        $this->beforeApplicationDestroyed(function () use ($database) {
            $connection = $database->connection();

            static::$incrementsPrev = $this->getIncrements();

            $connection->rollBack();
            $connection->disconnect();
        });
    }

    /**
     * Captures the current increments of all the tables that have an id column.
     */
    protected function getIncrements(): Collection
    {
        $tables = DB::select('SELECT TABLE_NAME FROM information_schema.columns WHERE COLUMN_NAME = "id" AND TABLE_SCHEMA = DATABASE()');

        return collect($tables)
            ->pluck('TABLE_NAME')
            ->mapWithKeys(fn ($t) => [$t => DB::table($t)->max('id') ?? 0]);
    }

    /**
     * Rollsback the captured increments.
     *
     *
     * @throws \Exeption
     */
    protected function rollbackIncrements(): void
    {
        if (! App::environment('testing')) {
            throw new Exception('Not allowed outside of testing.');
        }

        $tables = static::$incrementsStart
            ->diffAssoc(static::$incrementsPrev ?? collect());

        if ($tables->isEmpty()) {
            return;
        }

        $query = '';

        // Loop all the captured increments and reset the tables.
        foreach ($tables as $table => $increment) {
            $query .= sprintf(
                'ALTER TABLE %s AUTO_INCREMENT = %d; ',
                $table,
                $increment + 1,
            );
        }

        DB::unprepared($query);
    }
}
