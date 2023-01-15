<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Integrations;

use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider::boot()
 *
 * @covers \BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions
 */
class SupportedPhpExtensionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->partialMock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('get')
                ->once()
                ->passthru();
            $mock->shouldReceive('fetch')
                ->once()
                ->andReturn(<<<'RES'
                    apcu 8.1 8.2
                    pdo_mysql 8.2
                    pdo_pgsql 8.0 8.1
                    memcached 8.0
                    redis 8.1
                RES);
        });
    }

    public function providePhpVersions(): array
    {
        return [
            [['apcu', 'pdo_mysql'], '8.2'],
            [['apcu', 'pdo_pgsql', 'redis'], '8.1'],
            [['pdo_pgsql', 'memcached'], '8.0'],
        ];
    }

    /** @dataProvider providePhpVersions */
    public function testItFiltersVersions(): void
    {
        $supported = app(SupportedPhpExtensions::class)->get('8.2');

        self::assertEquals(['apcu', 'pdo_mysql'], $supported);
    }
}
