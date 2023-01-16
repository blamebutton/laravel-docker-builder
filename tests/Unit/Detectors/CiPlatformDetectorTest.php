<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\CiPlatformDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;

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

        if (! is_dir($path = base_path('.git'))) {
            mkdir($path);
        }

        if (file_exists($path = base_path('.git/config'))) {
            unlink($path);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($path = base_path('.git/config'))) {
            unlink($path);
        }

        if (is_dir($path = base_path('.git'))) {
            rmdir($path);
        }
    }

    public function testItDetectsGitHub(): void
    {
        file_put_contents(
            filename: base_path('.git/config'),
            data: "[remote \"origin\"]\n\turl = git@github.com:blamebutton/laravel-docker-builder.git",
        );

        $detected = app(CiPlatformDetector::class)->detect();

        self::assertEquals('github', $detected);
    }

    public function testItDetectsGitLab(): void
    {
        file_put_contents(
            filename: base_path('.git/config'),
            data: "[remote \"origin\"]\n\turl = git@gitlab.com:blamebutton/laravel-docker-builder.git",
        );

        $detected = app(CiPlatformDetector::class)->detect();

        self::assertEquals('gitlab', $detected);
    }

    public function testItReturnsFalseWithNoMatches(): void
    {
        file_put_contents(
            filename: base_path('.git/config'),
            data: "[remote \"origin\"]\n\turl = git@bitbucket.com:blamebutton/laravel-docker-builder.git",
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
