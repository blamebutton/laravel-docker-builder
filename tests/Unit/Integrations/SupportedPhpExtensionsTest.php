<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Integrations;

use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions
 */
class SupportedPhpExtensionsTest extends TestCase
{
    public function providePhpVersions(): array
    {
        return [
            [['apcu', 'pdo_mysql'], '8.2'],
            [['apcu', 'pdo_pgsql', 'redis'], '8.1'],
            [['pdo_pgsql', 'memcached'], '8.0'],
        ];
    }

    /** @dataProvider providePhpVersions */
    public function testItFiltersVersions($expected, $version): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) use ($version) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('get')
                ->with($version)
                ->once()
                ->passthru();
            $mock->shouldReceive('fetch')
                ->withNoArgs()
                ->once()
                ->andReturn([
                    'apcu 8.1 8.2',
                    'pdo_mysql 8.2',
                    'pdo_pgsql 8.0 8.1',
                    'memcached 8.0',
                    'redis 8.1',
                ]);
        });

        $supported = app(SupportedPhpExtensions::class)->get($version);

        self::assertEquals($expected, $supported);
    }

    public function testItReturnsEmptyArrayOnError(): void
    {
        $this->mock(SupportedPhpExtensions::class, function(MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('get')->once()->passthru();
            $mock->shouldReceive('fetch')->once()->withNoArgs()->andReturn(false);
        });

        $supported = app(SupportedPhpExtensions::class)->get('8.2');

        self::assertEquals([], $supported);
    }
}
