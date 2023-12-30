<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions\Choices;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager
 */
class NodePackageManagerTest extends TestCase
{
    public function testItReturnsCorrectlyOrderedValues(): void
    {
        self::assertEquals(['npm', 'yarn'], NodePackageManager::values());
    }

    public function testItReturnsCorrectNames(): void
    {
        self::assertEquals('NPM', NodePackageManager::name(NodePackageManager::NPM));
        self::assertEquals('Yarn', NodePackageManager::name(NodePackageManager::YARN));
    }
}
