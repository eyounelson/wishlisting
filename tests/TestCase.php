<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Shared\PreparesData;

abstract class TestCase extends BaseTestCase
{
    use PreparesData;
}
