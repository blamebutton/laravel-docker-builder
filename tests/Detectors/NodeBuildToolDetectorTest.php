<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\NodeBuildToolDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;

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

        File::delete([
            base_path('vite.config.js'),
            base_path('vite.config.ts'),
            base_path('webpack.mix.js'),
        ]);
    }

    public static function providePathMappings(): array
    {
        return [
            'vite js' => ['vite', 'vite.config.js'],
            'vite ts' => ['vite', 'vite.config.ts'],
            'mix' => ['mix', 'webpack.mix.js'],
            'unsupported' => [false, 'unsupported'],
        ];
    }

    #[DataProvider('providePathMappings')]
    public function testItDetectsPaths(string|bool $expected, string $filename): void
    {
        touch(base_path($filename));

        $detected = app(NodeBuildToolDetector::class)->detect();

        self::assertEquals($expected, $detected);
    }
}
