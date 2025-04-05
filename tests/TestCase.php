<?php

namespace SteadfastCollective\StatamicAuth\Tests;

use SteadfastCollective\StatamicAuth\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
