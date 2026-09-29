<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    /**
     * Proxy to the base `actingAs` to preserve default behavior for web tests.
     * Use `actingAsApi($user)` when you need the `api` guard for API routes.
     */
    public function actingAs($user, $guard = null)
    {
        return parent::actingAs($user, $guard);
    }

    /**
     * Authenticate the given user for the `api` guard specifically.
     */
    public function actingAsApi($user)
    {
        return parent::actingAs($user, 'api');
    }
}
