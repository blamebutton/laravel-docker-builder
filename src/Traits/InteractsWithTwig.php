<?php

namespace BlameButton\LaravelDockerBuilder\Traits;

use RuntimeException;
use Twig\Environment as TwigEnvironment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;

trait InteractsWithTwig
{
    private TwigEnvironment|null $twig = null;

    private function twig(): TwigEnvironment
    {
        if (! is_null($this->twig)) {
            return $this->twig;
        }

        $path = package_path('docker/template');
        $loader = new FilesystemLoader($path);

        return $this->twig = new TwigEnvironment($loader);
    }

    /**
     * Render a Twig template.
     *
     * @param  string  $name
     * @param  array<string, mixed>  $context
     * @return string
     */
    private function render(string $name, array $context): string
    {
        try {
            return $this->twig()->render($name, $context);
        } catch (Error $error) {
            throw new RuntimeException($error->getMessage());
        }
    }
}
