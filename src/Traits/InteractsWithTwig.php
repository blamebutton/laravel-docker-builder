<?php

namespace BlameButton\LaravelDockerBuilder\Traits;

use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

trait InteractsWithTwig
{
    private TwigEnvironment|null $twig = null;

    public function twig(): TwigEnvironment
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
     *
     * @throws LoaderError  when the template cannot be found
     * @throws SyntaxError  when an error occurred during compilation
     * @throws RuntimeError when an error occurred during rendering
     */
    public function render(string $name, array $context): string
    {
        return $this->twig()->render($name, $context);
    }
}
