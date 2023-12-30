<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Integrations;

use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions
 */
class SupportedPhpExtensionsTest extends TestCase
{
    public static function providePhpVersions(): array
    {
        return [
            [['apcu', 'pdo_mysql'], '8.2'],
            [['apcu', 'pdo_pgsql', 'redis'], '8.1'],
            [['pdo_pgsql', 'memcached'], '8.0'],
        ];
    }

    #[DataProvider('providePhpVersions')]
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
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('get')->once()->passthru();
            $mock->shouldReceive('fetch')->once()->withNoArgs()->andReturn(false);
        });

        $supported = app(SupportedPhpExtensions::class)->get('8.2');

        self::assertEquals([], $supported);
    }

    public function testItCachesResponse(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('get')->twice()->passthru();
            $mock->shouldReceive('fetch')->once()->andReturn();
        });

        app(SupportedPhpExtensions::class)->get();
        app(SupportedPhpExtensions::class)->get();
    }

    public function testItReturnsFalseOnError(): void
    {
        Http::fake([
            'github.com/*' => Http::response("bcmath\nmemcached\n", 500),
        ]);

        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('fetch')->once()->passthru();
        });

        $response = app(SupportedPhpExtensions::class)->fetch();

        self::assertFalse($response);
    }

    public function testItReturnsExtensions(): void
    {
        Http::fake([
            'github.com/*' => Http::response("bcmath\nmemcached\n"),
        ]);

        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('fetch')->once()->passthru();
        });

        $response = app(SupportedPhpExtensions::class)->fetch();

        self::assertEquals(['bcmath', 'memcached'], $response);
    }
}
