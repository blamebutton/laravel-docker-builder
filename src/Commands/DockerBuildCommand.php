<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use BlameButton\LaravelDockerBuilder\DockerServiceProvider;
use Symfony\Component\Process\Process;

class DockerBuildCommand extends BaseCommand
{
    protected $name = 'docker:build';

    public function handle(): int
    {
        $command = package_path('bin/docker-build');

        $process = new Process(
            command: [$command],
            cwd: base_path(),
        );

        $process->run(function ($type, $buffer) {
            match ($type) {
                Process::OUT => fwrite(STDOUT, $buffer),
                Process::ERR => fwrite(STDERR, $buffer),
            };
        });

        return self::SUCCESS;
    }
}