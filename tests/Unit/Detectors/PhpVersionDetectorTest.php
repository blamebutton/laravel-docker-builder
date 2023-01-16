<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\PhpVersionDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Composer\Semver\VersionParser;
use Mockery\MockInterface;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion
 *
 * @covers \BlameButton\LaravelDockerBuilder\Detectors\PhpVersionDetector
 */
class PhpVersionDetectorTest extends TestCase
{
    public function testItReturnsFalseWhenNoComposerFileWasFound(): void
    {
        $this->partialMock(PhpVersionDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('getComposerFileContents')
                ->once()
                ->andReturn(false);
        });

        $detected = app(PhpVersionDetector::class)->detect();

        self::assertFalse($detected);
    }

    public function testItReturnsFalseWhenNoPhpVersionWasFound(): void
    {
        $this->partialMock(PhpVersionDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('getComposerFileContents')
                ->once()
                ->andReturn('{ "require": {} }');
        });

        $detected = app(PhpVersionDetector::class)->detect();

        self::assertFalse($detected);
    }

    public function provideVersions(): array
    {
        return [
            ['8.2', '^8.2'],
            ['8.2', '>=8.2'],
            ['8.1', '~8.1'],
            ['8.1', '8.1.*'],
            ['8.0', '^8.0.2'],
            ['8.0', '8.0.24'],
        ];
    }

    /** @dataProvider provideVersions */
    public function testItParsesJsonVersion($expected, string $version): void
    {
        $this->partialMock(PhpVersionDetector::class, function (MockInterface $mock) use ($version) {
            $mock->shouldReceive('getComposerFileContents')
                ->once()
                ->andReturn(sprintf('{ "require": { "php": "%s" } }', $version));
        });

        $this->partialMock(VersionParser::class, function (MockInterface $mock) use ($version) {
            $mock->shouldReceive('parseConstraints')
                ->with($version)
                ->once()
                ->passthru();
        });

        $detected = app(PhpVersionDetector::class)->detect();

        self::assertEquals($expected, $detected);
    }

    public function testItGetsComposerFileContents(): void
    {
        $contents = app(PhpVersionDetector::class)->getComposerFileContents();

        self::assertJson($contents);

        $json = json_decode($contents);

        self::assertEquals('laravel/laravel', data_get($json, 'name'));
    }

    public function testItReturnsFalseOnMissingComposerFile(): void
    {
        $this->app->setBasePath('non-existent');

        $detected = app(PhpVersionDetector::class)->getComposerFileContents();

        self::assertFalse($detected);
    }
}
