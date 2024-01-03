<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Integrations;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
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
            [['apcu', 'pdo_mysql'], PhpVersion::v8_3],
            [['apcu', 'pdo_pgsql', 'redis'], PhpVersion::v8_2],
            [['pdo_pgsql', 'memcached'], PhpVersion::v8_1],
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
                    'apcu 8.2 8.3',
                    'pdo_mysql 8.3',
                    'pdo_pgsql 8.1 8.2',
                    'memcached 8.1',
                    'redis 8.2',
                ]);
        });

        $supported = app(SupportedPhpExtensions::class)->get($version);

        self::assertEquals($expected, $supported);
    }

    public function testItReturnsEmptyArrayOnError(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (SupportedPhpExtensions&MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('get')->once()->with(PhpVersion::v8_3)->passthru();
            $mock->shouldReceive('fetch')->once()->withNoArgs()->andReturn(false);
        });

        $supported = app(SupportedPhpExtensions::class)->get(PhpVersion::v8_3);

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
