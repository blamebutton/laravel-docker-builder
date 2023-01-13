<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

class NodeBuildTool
{
    public const VITE = 'vite';

    public const MIX = 'mix';

    public static function values(): array
    {
        return [
            self::VITE,
            self::MIX,
        ];
    }

    public static function name(string $value): string
    {
        return match ($value) {
            self::VITE => 'Vite.js',
            self::MIX => 'Laravel mix',
        };
    }
}
