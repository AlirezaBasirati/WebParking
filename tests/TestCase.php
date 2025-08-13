<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up any common test configuration here
        // Removed withoutExceptionHandling() to allow proper exception handling
    }
}
