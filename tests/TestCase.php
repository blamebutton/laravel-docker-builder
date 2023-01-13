<?php

namespace BlameButton\LaravelDockerBuilder\Tests;

use BlameButton\LaravelDockerBuilder\DockerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

/**
 */
class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DockerServiceProvider::class,
        ];
    }
}
