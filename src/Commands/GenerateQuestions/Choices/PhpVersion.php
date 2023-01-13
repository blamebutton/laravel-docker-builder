<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

class PhpVersion
{
    public const v8_2 = '8.2';

    public const v8_1 = '8.1';

    public const v8_0 = '8.0';

    public static function values(): array
    {
        return [
            self::v8_2,
            self::v8_1,
            self::v8_0,
        ];
    }
}
