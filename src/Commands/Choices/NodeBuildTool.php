<?php

namespace BlameButton\LaravelDockerBuilder\Commands\Choices;

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
}