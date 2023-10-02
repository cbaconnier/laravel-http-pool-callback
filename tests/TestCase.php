<?php

namespace Cbaconnier\HttpPool\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{

    protected function getPackageProviders($app): array
    {
        return [
            \Cbaconnier\HttpPool\HttpPoolServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'HttpPool' => \Cbaconnier\HttpPool\Facades\HttpPool::class,
        ];
    }

}
