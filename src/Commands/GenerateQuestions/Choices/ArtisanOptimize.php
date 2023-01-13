<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

class ArtisanOptimize
{
    public const YES = 'yes';

    public const NO = 'no';

    public static function values(): array
    {
        return [
            self::YES,
            self::NO,
        ];
    }
}
