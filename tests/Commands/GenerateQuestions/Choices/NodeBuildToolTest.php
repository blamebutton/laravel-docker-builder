<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions\Choices;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool
 */
class NodeBuildToolTest extends TestCase
{
    public function testItReturnsCorrectlyOrderedValues(): void
    {
        self::assertEquals(['vite', 'mix'], NodeBuildTool::values());
    }

    public function testItReturnsCorrectNames(): void
    {
        self::assertEquals('Vite.js', NodeBuildTool::name(NodeBuildTool::VITE));
        self::assertEquals('Laravel Mix', NodeBuildTool::name(NodeBuildTool::MIX));
    }
}
