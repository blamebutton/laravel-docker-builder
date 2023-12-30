<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions\Choices;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform
 */
class CiPlatformTest extends TestCase
{
    public function testItReturnsCorrectlyOrderedValues(): void
    {
        self::assertEquals(['github', 'gitlab'], CiPlatform::values());
    }

    public function testItReturnsCorrectNames(): void
    {
        self::assertEquals('GitHub Actions', CiPlatform::name(CiPlatform::GITHUB_ACTIONS));
        self::assertEquals('GitLab CI/CD', CiPlatform::name(CiPlatform::GITLAB_CI));
    }
}
