<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * @covers package_path()
 */
class HelpersTest extends TestCase
{
    public function testItRegistersFunction(): void
    {
        self::assertTrue(function_exists('package_path'));
    }

    public function testItReturnsPackagePath(): void
    {
        $path = package_path();

        self::assertEquals(dirname(__FILE__, 3), $path);
    }

    public function testItReturnsPackagePathSubPath(): void
    {
        $path = package_path('composer.json');

        self::assertEquals(dirname(__FILE__, 3).'/composer.json', $path);
    }
}
