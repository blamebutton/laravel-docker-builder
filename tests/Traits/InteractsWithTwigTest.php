<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Traits;

use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use BlameButton\LaravelDockerBuilder\Traits\InteractsWithTwig;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses package_path()
 *
 * @covers \BlameButton\LaravelDockerBuilder\Traits\InteractsWithTwig
 */
class InteractsWithTwigTest extends TestCase
{
    public function testItCreatesTwig(): void
    {
        /** @var InteractsWithTwig $class */
        $class = $this->getObjectForTrait(InteractsWithTwig::class);

        $twig = $class->twig();
        self::assertInstanceOf(Environment::class, $twig);
        $loader = $twig->getLoader();
        self::assertInstanceOf(FilesystemLoader::class, $loader);
        self::assertEquals([package_path('docker/template')], $loader->getPaths());
    }

    public function testItThrowsErrorOnMissingTemplates(): void
    {
        /** @var InteractsWithTwig $class */
        $class = $this->getObjectForTrait(InteractsWithTwig::class);

        $this->expectException(LoaderError::class);

        $class->render('invalid-filename', []);
    }

    public function testItRendersNginxTemplate(): void
    {
        /** @var InteractsWithTwig $class */
        $class = $this->getObjectForTrait(InteractsWithTwig::class);

        $output = $class->render('nginx.dockerfile.twig', []);

        self::assertStringContainsString('FROM nginx:1-alpine', $output);
    }

    public function testItRendersPhpTemplate(): void
    {
        /** @var InteractsWithTwig $class */
        $class = $this->getObjectForTrait(InteractsWithTwig::class);

        $output = $class->render('php.dockerfile.twig', [
            'php_version' => '8.2',
        ]);

        self::assertStringContainsString('FROM php:8.2-fpm-alpine', $output);
    }
}
