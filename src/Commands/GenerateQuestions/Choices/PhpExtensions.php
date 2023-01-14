<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;

class PhpExtensions
{
    public static function values(string $phpVersion = null): array
    {
        return app(SupportedPhpExtensions::class)->fetch($phpVersion);
    }
}
