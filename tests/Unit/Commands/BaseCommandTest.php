<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\BaseCommand
 */
class BaseCommandTest extends TestCase
{
    public function testOptionalChoiceTreatsNoneAsFalse(): void
    {
        $mock = $this->createPartialMock(BaseCommand::class, ['choice']);
        $mock->expects($this->once())
            ->method('choice')
            ->with('question', ['an-option', 'none'], 'an-option')
            ->willReturn('none');

        $answer = $mock->optionalChoice('question', ['an-option'], 'an-option');

        self::assertFalse($answer);
    }
}
