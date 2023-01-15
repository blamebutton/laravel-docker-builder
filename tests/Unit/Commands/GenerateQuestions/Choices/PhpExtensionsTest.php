<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands\GenerateQuestions\Choices;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions;
use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider::boot()
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions
 */
class PhpExtensionsTest extends TestCase
{
    public function testItCallsSupportedPhpExtensions(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetch')->once()->andReturn([
                'bcmath',
                'pdo_mysql',
                'redis',
            ]);
        });

        $extensions = PhpExtensions::values();

        self::assertEquals(['bcmath', 'pdo_mysql', 'redis'], $extensions);
    }
}
