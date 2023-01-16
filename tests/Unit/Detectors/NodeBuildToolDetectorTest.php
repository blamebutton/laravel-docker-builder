<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\NodeBuildToolDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses \BlameButton\LaravelDockerBuilder\Detectors\FileDetector
 *
 * @covers \BlameButton\LaravelDockerBuilder\Detectors\NodeBuildToolDetector
 */
class NodeBuildToolDetectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        collect(['vite.config.js', 'vite.config.ts', 'webpack.mix.js'])
            ->map(fn ($file) => base_path($file))
            ->filter(fn ($file) => file_exists($file))
            ->each(fn ($file) => unlink($file));
    }

    public function providePathMappings(): array
    {
        return [
            'vite js' => ['vite', 'vite.config.js'],
            'vite ts' => ['vite', 'vite.config.ts'],
            'mix' => ['mix', 'webpack.mix.js'],
            'unsupported' => [false, 'unsupported'],
        ];
    }

    /** @dataProvider providePathMappings */
    public function testItDetectsPaths(string|bool $expected, string $filename): void
    {
        touch(base_path($filename));

        $detected = app(NodeBuildToolDetector::class)->detect();

        self::assertEquals($expected, $detected);
    }
}
