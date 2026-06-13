<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests exercise web routes directly and should not fail on CSRF.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
