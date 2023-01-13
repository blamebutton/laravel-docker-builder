<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

class PhpExtensions
{
    private const SUPPORTED_EXTENSIONS = 'https://github.com/mlocati/docker-php-extension-installer/raw/master/data/supported-extensions';

    private static array|null $cache = null;

    public static function values(string $phpVersion = null): array
    {
        if (! is_null(self::$cache)) {
            return self::$cache;
        }

        try {
            $contents = file_get_contents(self::SUPPORTED_EXTENSIONS);

            return self::$cache = str($contents)
                ->explode("\n")
                ->filter(fn (string $extension): bool => is_null($phpVersion) || str($extension)->contains($phpVersion))
                ->map(fn (string $extension): string => str($extension)->trim()->before(' '))
                ->filter()
                ->toArray();
        } catch (\ErrorException) {
            return [];
        }
    }
}
