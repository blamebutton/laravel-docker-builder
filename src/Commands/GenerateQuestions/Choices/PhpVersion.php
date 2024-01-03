<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

enum PhpVersion: string
{
    case v8_3 = '8.3';
    case v8_2 = '8.2';
    case v8_1 = '8.1';

    public static function values(): array
    {
        return array_map(fn (self $version) => $version->value, self::cases());
    }

    public function label(): string
    {
        return $this->value;
    }
}
