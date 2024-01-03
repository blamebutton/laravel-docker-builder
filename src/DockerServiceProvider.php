<?php

namespace BlameButton\LaravelDockerBuilder;

use BlameButton\LaravelDockerBuilder\Commands\DockerBuildCommand;
use BlameButton\LaravelDockerBuilder\Commands\DockerCiCommand;
use BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand;
use BlameButton\LaravelDockerBuilder\Commands\DockerPushCommand;
use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use Illuminate\Support\ServiceProvider;

class DockerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SupportedPhpExtensions::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DockerBuildCommand::class,
                DockerCiCommand::class,
                DockerGenerateCommand::class,
                DockerPushCommand::class,
            ]);
        }

        $this->publishes([
            dirname(__DIR__).'/config/docker-builder.php' => config_path('docker-builder.php'),
        ]);

        $this->mergeConfigFrom(
            dirname(__DIR__).'/config/docker-builder.php', 'docker-builder',
        );
    }
}
