<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\CiPlatformDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Detectors\CiPlatformDetector
 */
class CiPlatformDetectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        File::ensureDirectoryExists(base_path('.git'));
        File::delete(base_path('.git/config'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory(base_path('.git'));
    }

    public function testItDetectsGitHub(): void
    {
        File::put(
            path: base_path('.git/config'),
            contents: "[remote \"origin\"]\n\turl = git@github.com:blamebutton/laravel-docker-builder.git",
        );

        $detected = app(CiPlatformDetector::class)->detect();

        self::assertEquals('github', $detected);
    }

    public function testItDetectsGitLab(): void
    {
        File::put(
            path: base_path('.git/config'),
            contents: "[remote \"origin\"]\n\turl = git@gitlab.com:blamebutton/laravel-docker-builder.git",
        );

        $detected = app(CiPlatformDetector::class)->detect();

        self::assertEquals('gitlab', $detected);
    }

    public function testItReturnsFalseWithNoMatches(): void
    {
        File::put(
            path: base_path('.git/config'),
            contents: "[remote \"origin\"]\n\turl = git@bitbucket.com:blamebutton/laravel-docker-builder.git",
        );

        $detected = app(CiPlatformDetector::class)->detect();

        self::assertFalse($detected);
    }

    public function testItReturnsFalseWithMissingGitConfig(): void
    {
        $detected = app(CiPlatformDetector::class)->detect();

        self::assertFalse($detected);
    }
}
