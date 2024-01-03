<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;

class PhpExtensions
{
    public static function values(?PhpVersion $phpVersion = null): array
    {
        /** @var SupportedPhpExtensions $extensions */
        $extensions = app(SupportedPhpExtensions::class);

        return $extensions->get($phpVersion);
    }
}
