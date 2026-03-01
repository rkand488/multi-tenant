<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Wrap the central DB connection in a transaction per-test, so that
     * central table data (plans, subscriptions, etc.) is rolled back
     * automatically after each test — the same way the default connection is.
     *
     * We include 'sqlite' (the default connection alias) alongside 'central' so
     * that RefreshDatabase caches and restores both in-memory PDO connections.
     *
     * @var list<string>
     */
    protected $connectionsToTransact = ['sqlite', 'central'];
}
