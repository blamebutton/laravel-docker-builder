<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use Symfony\Component\Process\Process;

class DockerBuildCommand extends BaseDockerCommand
{
    protected $name = 'docker:build';

    protected $description = 'Build Docker images';

    public function handle(): int
    {
        $command = package_path('bin/docker-build');

        $process = new Process(
            command: [$command],
            cwd: base_path(),
            env: $this->getEnvironment(),
            timeout: null,
        );

        return $this->runProcess($process);
    }
}
