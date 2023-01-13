<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands;

use BlameButton\LaravelDockerBuilder\Commands\BaseDockerCommand;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;
use Symfony\Component\Process\Process;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\BaseDockerCommand
 */
class BaseDockerCommandTest extends TestCase
{
    public function testItUsesConfigurationValues(): void
    {
        $this->mock('config', function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->once()
                ->with('docker-builder.tags.nginx')
                ->andReturn('test:nginx');
            $mock->shouldReceive('get')
                ->once()
                ->with('docker-builder.tags.php')
                ->andReturn('test:php');
        });

        $class = $this->newBaseDockerCommand();
        $environment = $class->getEnvironment();

        self::assertEquals([
            'DOCKER_NGINX_TAG' => 'test:nginx',
            'DOCKER_PHP_TAG' => 'test:php',
        ], $environment);
    }

    public function testItRunsProcess(): void
    {
        $mock = $this->createMock(Process::class);
        $mock->expects($this->once())
            ->method('run')
            ->willReturnCallback(function ($callable) {
                $callable(Process::OUT, "stdout output\n");
                $callable(Process::ERR, "stderr output\n");

                return 0;
            });

        $class = $this->newBaseDockerCommand();
        $output = $class->runProcess(
            process: $mock,
            out: fopen('php://memory', 'r+'),
            err: fopen('php://memory', 'r+'),
        );

        self::assertEquals(0, $output);
    }

    private function newBaseDockerCommand(): BaseDockerCommand
    {
        return new class extends BaseDockerCommand
        {
        };
    }
}
