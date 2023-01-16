<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\FileDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Detectors\FileDetector
 */
class FileDetectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists($path = base_path('test-file'))) {
            unlink($path);
        }

        $this->mock(FileDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('detect')
                ->passthru();
            $mock->shouldReceive('getPathMapping')
                ->once()
                ->andReturn([
                    base_path('test-file') => 'test',
                ]);
        });
    }

    public function testItDetectsFileWhenPresent(): void
    {
        touch(base_path('test-file'));

        $detected = app(FileDetector::class)->detect();

        self::assertEquals('test', $detected);
    }

    public function testItReturnsFalseWhenFileMissing(): void
    {
        $detected = app(FileDetector::class)->detect();

        self::assertFalse($detected);
    }
}
