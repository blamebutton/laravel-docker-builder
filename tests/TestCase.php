<?php

namespace BlameButton\LaravelDockerBuilder\Tests;

use BlameButton\LaravelDockerBuilder\DockerServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders(Application $app): array
    {
        return [
            DockerServiceProvider::class,
        ];
    }
}
