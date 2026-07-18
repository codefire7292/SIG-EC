<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->afterApplicationCreated(function () {
            if ($this->app->bound(\Spatie\Permission\PermissionRegistrar::class)) {
                $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            }
        });
    }

    protected function tearDown(): void
    {
        if ($this->app && $this->app->bound(\Spatie\Permission\PermissionRegistrar::class)) {
            $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
        
        parent::tearDown();
    }
}
