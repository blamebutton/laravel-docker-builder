<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use Symfony\Component\Process\Process;

class DockerPushCommand extends BaseDockerCommand
{
    protected $name = 'docker:push';

    protected $description = 'Push Docker images';

    public function handle(): int
    {
        $command = package_path('bin/docker-push');

        $process = new Process(
            command: [$command],
            cwd: base_path(),
            env: $this->getEnvironment(),
            timeout: null,
        );

        return $this->runProcess($process);
    }
}