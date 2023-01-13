<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use Symfony\Component\Process\Process;

abstract class BaseDockerCommand extends BaseCommand
{
    public function getEnvironment(): array
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = app('config');

        return [
            'DOCKER_NGINX_TAG' => $config->get('docker-builder.tags.nginx'),
            'DOCKER_PHP_TAG' => $config->get('docker-builder.tags.php'),
        ];
    }

    public function runProcess(Process $process, $out = STDOUT, $err = STDERR): int
    {
        return $process->run(function ($type, $buffer) use ($out, $err) {
            match ($type) {
                Process::OUT => fwrite($out, $buffer),
                Process::ERR => fwrite($err, $buffer),
            };
        });
    }
}
