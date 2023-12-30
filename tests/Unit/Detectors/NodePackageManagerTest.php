<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\NodePackageManagerDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;

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

        File::delete([
            base_path('package-lock.json'),
            base_path('yarn.lock'),
        ]);
    }

    public static function providePathMappings(): array
    {
        return [
            'npm' => ['npm', 'package-lock.json'],
            'yarn' => ['yarn', 'yarn.lock'],
        ];
    }

    #[DataProvider('providePathMappings')]
    public function testItDetectsPaths(string|bool $expected, string $filename): void
    {
        touch(base_path($filename));

        $detected = app(NodePackageManagerDetector::class)->detect();

        self::assertEquals($expected, $detected);
    }
}
