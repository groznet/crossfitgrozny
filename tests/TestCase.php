<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refuse to run against anything but the in-memory SQLite database.
     *
     * A cached config (php artisan config:cache / optimize) makes Laravel
     * ignore phpunit.xml, so tests would otherwise run RefreshDatabase
     * against the real database and wipe it.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if ($app['config']->get('database.default') !== 'sqlite' || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException(
                'Tests must run on in-memory SQLite. Your config looks cached: run "php artisan optimize:clear" and try again.'
            );
        }

        return $app;
    }
}
