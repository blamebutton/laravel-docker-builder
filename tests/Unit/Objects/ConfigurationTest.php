<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Objects;

use BlameButton\LaravelDockerBuilder\Objects\Configuration;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Objects\Configuration
 */
class ConfigurationTest extends TestCase
{
    public static function provideCommandOptions(): array
    {
        return [
            [
                'php artisan docker:generate -n -p 8.2 -e bcmath,pdo_mysql -o -a -m npm -b vite',
                new Configuration('8.2', ['bcmath', 'pdo_mysql'], true, true, 'npm', 'vite'),
            ],
            [
                'php artisan docker:generate -n -p 8.1 -e bcmath,pdo_pgsql,redis -o -m yarn -b vite',
                new Configuration('8.1', ['bcmath', 'pdo_pgsql', 'redis'], true, false, 'yarn', 'vite'),
            ],
            [
                'php artisan docker:generate -n -p 8.0 -e bcmath,pdo_pgsql,apcu -a -m yarn -b mix',
                new Configuration('8.0', ['bcmath', 'pdo_pgsql', 'apcu'], false, true, 'yarn', 'mix'),
            ],
        ];
    }

    public function testItConstructs(): void
    {
        $config = new Configuration('8.2', ['bcmath'], true, true, 'npm', 'vite');

        self::assertEquals('8.2', $config->getPhpVersion());
        self::assertEquals(['bcmath'], $config->getPhpExtensions());
        self::assertTrue($config->isArtisanOptimize());
        self::assertTrue($config->isAlpine());
        self::assertEquals('npm', $config->getNodePackageManager());
        self::assertEquals('vite', $config->getNodeBuildTool());
    }

    #[DataProvider('provideCommandOptions')]
    public function testItGeneratesCorrectCommand(string $expected, Configuration $config): void
    {
        $output = $config->getCommand();

        self::assertEquals($expected, implode(' ', $output));
    }
}
