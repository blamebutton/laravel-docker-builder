<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit;

use BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Contracts\Console\Kernel;
use Mockery\MockInterface;

/**
 * @uses \BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand::getOptions()
 * @uses \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion
 * @uses \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions
 * @uses \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager
 * @uses \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool
 *
 * @covers \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 */
class DockerServiceProviderTest extends TestCase
{
    public function testItRegistersCommands(): void
    {
        $this->partialMock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->once()->andReturn([]);
        });

        $commands = app(Kernel::class)->all();

        self::assertArrayHasKey('docker:build', $commands);
        self::assertArrayHasKey('docker:ci', $commands);
        self::assertArrayHasKey('docker:generate', $commands);
        self::assertArrayHasKey('docker:push', $commands);
    }
}