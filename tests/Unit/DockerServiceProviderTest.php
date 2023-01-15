<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit;

use BlameButton\LaravelDockerBuilder\DockerServiceProvider;
use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\ServiceProvider;
use Mockery\MockInterface;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\DockerCiCommand::getArguments()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand::getOptions()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool
 * @uses   package_path()
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

    public function testItPublishesConfig(): void
    {
        self::assertArrayHasKey(DockerServiceProvider::class, ServiceProvider::$publishes);
        self::assertEquals([
            package_path('config/docker-builder.php') => base_path('config/docker-builder.php'),
        ], ServiceProvider::$publishes[DockerServiceProvider::class]);
    }
}
