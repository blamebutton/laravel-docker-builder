<?php

namespace BlameButton\LaravelDockerBuilder;

use BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand;
use Illuminate\Support\ServiceProvider;

class DockerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DockerGenerateCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->app->instance('laravel-docker-builder.base_path', dirname(__DIR__));
    }
}