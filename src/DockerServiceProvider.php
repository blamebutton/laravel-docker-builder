<?php

namespace BlameButton\LaravelDockerBuilder;

use BlameButton\LaravelDockerBuilder\Commands\DockerBuildCommand;
use BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand;
use Illuminate\Support\ServiceProvider;

class DockerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DockerBuildCommand::class,
                DockerGenerateCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/docker-builder.php' => config_path('docker-builder.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/docker-builder.php', 'docker-builder',
        );
    }

    public static function getPackagePath(string $path = null): string
    {
        $dir = dirname(__FILE__, 2);
        if ($path) {
            return $dir.DIRECTORY_SEPARATOR.$path;
        }

        return $dir;
    }
}
