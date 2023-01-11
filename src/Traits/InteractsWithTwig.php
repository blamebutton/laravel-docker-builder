<?php

namespace BlameButton\LaravelDockerBuilder\Traits;

use Twig\Environment as TwigEnvironment;
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

    private function render(string $name, array $context): string
    {
        return $this->twig()->render($name, $context);
    }
}
