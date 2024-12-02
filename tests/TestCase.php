<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Load testing database configuration
        $config = require __DIR__ . '/../config/database.testing.php';
        config()->set('database', $config);

        // Disable foreign key checks
       // $this->app['db']->connection()->getSchemaBuilder()->disableForeignKeyConstraints();

        // Run migrations
        $this->artisan('migrate:fresh', [
            '--path' => 'database/migrations',
            '--no-interaction' => true,
        ]);

        // Re-enable foreign key checks
        $this->app['db']->connection()->getSchemaBuilder()->enableForeignKeyConstraints();
    }
}
