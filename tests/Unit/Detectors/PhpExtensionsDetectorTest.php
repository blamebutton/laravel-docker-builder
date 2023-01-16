<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Detectors;

use BlameButton\LaravelDockerBuilder\Detectors\PhpExtensionsDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Detectors\PhpExtensionsDetector
 */
class PhpExtensionsDetectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(PhpExtensionsDetector::class, function () {
            $supported = [
                'apcu',
                'bcmath',
                'memcached',
                'pdo_mysql',
                'pdo_pgsql',
                'pdo_sqlsrv',
                'redis',
                'sqlsrv',
            ];

            return (new PhpExtensionsDetector())
                ->supported($supported);
        });
    }

    public function provideConfigurations(): array
    {
        return [
            [
                ['bcmath'],
                [
                    'cache.default' => 'array',
                    'cache.stores.array.driver' => 'array',
                    'cache.stores.redis.driver' => 'redis',
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.driver' => 'sqlite',
                    'broadcasting.default' => 'log',
                    'broadcasting.connections.log.driver' => 'log',
                    'queue.default' => 'sync',
                    'queue.connections.sync.driver' => 'sync',
                    'session.driver' => 'array',
                ],
            ],
            [
                ['apcu','bcmath'],
                [
                    'cache.default' => 'apc',
                    'cache.stores.apc.driver' => 'apc',
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.driver' => 'sqlite',
                    'broadcasting.default' => 'log',
                    'broadcasting.connections.log.driver' => 'log',
                    'queue.default' => 'sync',
                    'queue.connections.sync.driver' => 'sync',
                    'session.driver' => 'apc',
                ],
            ],
            [
                ['bcmath', 'redis'],
                [
                    'cache.default' => 'array',
                    'cache.stores.array.driver' => 'array',
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.driver' => 'sqlite',
                    'broadcasting.default' => 'log',
                    'broadcasting.connections.log.driver' => 'log',
                    'queue.default' => 'redis',
                    'queue.connections.redis.driver' => 'redis',
                    'session.driver' => 'array',
                ],
            ],
            [
                ['bcmath', 'redis'],
                [
                    'cache.default' => 'array',
                    'cache.stores.array.driver' => 'array',
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.driver' => 'sqlite',
                    'broadcasting.default' => 'redis',
                    'broadcasting.connections.redis.driver' => 'redis',
                    'queue.default' => 'sync',
                    'queue.connections.redis.driver' => 'sync',
                    'session.driver' => 'array',
                ],
            ],
            [
                ['apcu', 'bcmath','pdo_mysql'],
                [
                    'cache.default' => 'apc',
                    'cache.stores.apc.driver' => 'apc',
                    'database.default' => 'mysql',
                    'database.connections.mysql.driver' => 'mysql',
                    'broadcasting.default' => 'log',
                    'broadcasting.connections.log.driver' => 'log',
                    'queue.default' => 'sync',
                    'queue.connections.sync.driver' => 'sync',
                    'session.driver' => 'apc',
                ],
            ],
            [
                ['bcmath','memcached','pdo_sqlsrv','redis','sqlsrv'],
                [
                    'cache.default' => 'memcached',
                    'cache.stores.memcached.driver' => 'memcached',
                    'cache.stores.redis.driver' => 'redis',
                    'database.default' => 'sqlsrv',
                    'database.connections.sqlsrv.driver' => 'sqlsrv',
                    'broadcasting.default' => 'log',
                    'broadcasting.connections.log.driver' => 'log',
                    'queue.default' => 'sync',
                    'queue.connections.sync.driver' => 'sync',
                    'session.driver' => 'redis',
                ],
            ],
        ];
    }

    /** @dataProvider provideConfigurations */
    public function testItDetectsExtensionsWithoutDuplicates(array $expected, array $config): void
    {
        config()->set($config);

        $detected = app(PhpExtensionsDetector::class)->detect();

        self::assertEquals($expected, $detected);
    }

    public function testItReturnsDefaultExtensions(): void
    {
        $detected = app(PhpExtensionsDetector::class)->getDefaultExtensions();

        self::assertEquals(['bcmath'], $detected);
    }

    public function provideCacheConfigurations(): array
    {
        return [
            'apc' => [
                ['apcu'],
                ['cache.default' => 'apc', 'cache.stores.apc.driver' => 'apc'],
            ],
            'memcached' => [
                ['memcached'],
                ['cache.default' => 'memcached', 'cache.stores.memcached.driver' => 'memcached'],
            ],
            'redis' => [
                ['redis'],
                ['cache.default' => 'redis', 'cache.stores.redis.driver' => 'redis'],
            ],
            'array' => [
                [],
                ['cache.default' => 'array', 'cache.stores.array.driver' => 'array'],
            ],
        ];
    }

    /** @dataProvider provideCacheConfigurations */
    public function testItDetectsCacheExtensions(array $expected, array $config): void
    {
        config()->set($config);

        $detected = app(PhpExtensionsDetector::class)->getCacheExtensions();

        self::assertEquals($expected, $detected);
    }

    public function provideDatabaseConfigurations(): array
    {
        return [
            'mysql' => [
                ['pdo_mysql'],
                ['database.default' => 'mysql', 'database.connections.mysql.driver' => 'mysql'],
            ],
            'pgsql' => [
                ['pdo_pgsql'],
                ['database.default' => 'pgsql', 'database.connections.pgsql.driver' => 'pgsql'],
            ],
            'sqlsrv' => [
                ['pdo_sqlsrv', 'sqlsrv'],
                ['database.default' => 'sqlsrv', 'database.connections.sqlsrv.driver' => 'sqlsrv'],
            ],
            'sqlite' => [
                [],
                ['database.default' => 'sqlite', 'database.connections.sqlite.driver' => 'sqlite'],
            ],
        ];
    }

    /** @dataProvider provideDatabaseConfigurations */
    public function testItDetectsDatabaseExtensions(array $expected, array $config): void
    {
        config()->set($config);

        $detected = app(PhpExtensionsDetector::class)->getDatabaseExtensions();

        self::assertEquals($expected, $detected);
    }

    public function provideBroadcastingConfigurations(): array
    {
        return [
            'redis' => [
                ['redis'],
                ['broadcasting.default' => 'redis', 'broadcasting.connections.redis.driver' => 'redis'],
            ],
            'log' => [
                [],
                ['broadcasting.default' => 'log', 'database.connections.log.driver' => 'log'],
            ],
        ];
    }

    /** @dataProvider provideBroadcastingConfigurations */
    public function testItDetectsBroadcastingExtensions(array $expected, array $config): void
    {
        config()->set($config);

        $detected = app(PhpExtensionsDetector::class)->getBroadcastingExtensions();

        self::assertEquals($expected, $detected);
    }

    public function provideQueueConfigurations(): array
    {
        return [
            'redis' => [
                ['redis'],
                ['queue.default' => 'redis', 'queue.connections.redis.driver' => 'redis'],
            ],
            'sync' => [
                [],
                ['queue.default' => 'sync', 'queue.connections.sync.driver' => 'sync'],
            ],
        ];
    }

    /** @dataProvider provideQueueConfigurations */
    public function testItDetectsQueueExtensions(array $expected, array $config): void
    {
        config()->set($config);

        $detected = app(PhpExtensionsDetector::class)->getQueueExtensions();

        self::assertEquals($expected, $detected);
    }

    public function provideSessionConfigurations(): array
    {
        return [
            'apc' => [
                ['apcu'],
                ['session.driver' => 'apc', 'cache.stores.apc.driver' => 'apc'],
            ],
            'memcached' => [
                ['memcached'],
                ['session.driver' => 'memcached', 'cache.stores.memcached.driver' => 'memcached'],
            ],
            'redis' => [
                ['redis'],
                ['session.driver' => 'redis', 'cache.stores.redis.driver' => 'redis'],
            ],
            'array' => [
                [],
                ['session.driver' => 'array', 'cache.stores.array.driver' => 'array'],
            ],
        ];
    }

    /** @dataProvider provideSessionConfigurations */
    public function testItDetectsSessionExtensions(array $expected, array $config): void
    {
        config()->set($config);

        $detected = app(PhpExtensionsDetector::class)->getSessionExtensions();

        self::assertEquals($expected, $detected);
    }
}
