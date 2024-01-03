<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions\Choices;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion
 */
class PhpVersionTest extends TestCase
{
    public function testItReturnsCorrectlyOrderedValues(): void
    {
        self::assertEquals(['8.3', '8.2', '8.1'], PhpVersion::values());
    }
}
