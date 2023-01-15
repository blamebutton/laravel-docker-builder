<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands;

use BlameButton\LaravelDockerBuilder\Commands\DockerPushCommand;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Symfony\Component\Process\Process;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses package_path()
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\DockerPushCommand
 */
class DockerPushCommandTest extends TestCase
{
    public function testItRunsProcess(): void
    {
        $mock = $this->createPartialMock(DockerPushCommand::class, [
            'getEnvironment',
            'runProcess',
        ]);

        $environment = [
            'DOCKER_NGINX_TAG' => 'test:nginx',
            'DOCKER_PHP_TAG' => 'test:php',
        ];

        $mock->expects($this->once())
            ->method('getEnvironment')
            ->willReturn($environment);

        $mock->expects($this->once())
            ->method('runProcess')
            ->with($this->callback(
                function (Process $process) use ($environment): bool {
                    $command = "'".package_path('bin/docker-push')."'";

                    return $process->getCommandLine() === $command
                        && $process->getEnv() === $environment;
                }
            ))
            ->willReturn(0);

        $output = $mock->handle();

        self::assertEquals(0, $output);
    }
}
