<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\NodePackageManagerDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses \BlameButton\LaravelDockerBuilder\Detectors\FileDetector
 *
 * @covers \BlameButton\LaravelDockerBuilder\Detectors\NodePackageManagerDetector
 */
class NodePackageManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        collect(['package-lock.json', 'yarn.lock'])
            ->map(fn ($file) => base_path($file))
            ->filter(fn ($file) => file_exists($file))
            ->each(fn ($file) => unlink($file));
    }

    public function providePathMappings(): array
    {
        return [
            'npm' => ['npm', 'package-lock.json'],
            'yarn' => ['yarn', 'yarn.lock'],
        ];
    }

    /** @dataProvider providePathMappings */
    public function testItDetectsPaths(string|bool $expected, string $filename): void
    {
        touch(base_path($filename));

        $detected = app(NodePackageManagerDetector::class)->detect();

        self::assertEquals($expected, $detected);
    }
}
