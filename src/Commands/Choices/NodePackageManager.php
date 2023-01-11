<?php

namespace BlameButton\LaravelDockerBuilder\Commands\Choices;

class NodePackageManager
{
    public const NPM = 'npm';

    public const YARN = 'yarn';

    public static function values(): array
    {
        return [
            self::NPM,
            self::YARN,
        ];
    }
}
