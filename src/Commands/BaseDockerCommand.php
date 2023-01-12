<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use Symfony\Component\Process\Process;

abstract class BaseDockerCommand extends BaseCommand
{
    protected function getEnvironment(): array
    {
        return [
            'DOCKER_NGINX_TAG' => config('docker-builder.tags.nginx'),
            'DOCKER_PHP_TAG' => config('docker-builder.tags.php'),
        ];
    }

    protected function runProcess(Process $process): int
    {
        return $process->run(function ($type, $buffer) {
            match ($type) {
                Process::OUT => fwrite(STDOUT, $buffer),
                Process::ERR => fwrite(STDERR, $buffer),
            };
        });
    }
}