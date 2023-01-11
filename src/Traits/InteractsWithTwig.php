<?php

namespace BlameButton\LaravelDockerBuilder\Traits;

use Illuminate\Support\Facades\App;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

trait InteractsWithTwig
{
    private TwigEnvironment|null $twig = null;

    private function twig(): TwigEnvironment
    {
        if (!is_null($this->twig)) {
            return $this->twig;
        }
        $loader = new FilesystemLoader(App::get('laravel-docker-builder.base_path') . '/docker/template');
        return $this->twig = new TwigEnvironment($loader);
    }

    private function render(string $name, array $context): string
    {
        return $this->twig()->render($name, $context);
    }
}