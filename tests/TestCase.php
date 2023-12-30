<?php

namespace BlameButton\LaravelDockerBuilder\Tests;

use BlameButton\LaravelDockerBuilder\DockerServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param  Application  $app
     * @return array<int, class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [
            DockerServiceProvider::class,
        ];
    }
}
