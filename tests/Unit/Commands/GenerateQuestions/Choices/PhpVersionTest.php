<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands\GenerateQuestions\Choices;

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
        self::assertEquals(['8.2', '8.1', '8.0'], PhpVersion::values());
    }
}
