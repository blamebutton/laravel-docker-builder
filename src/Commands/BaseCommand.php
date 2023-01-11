<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use Illuminate\Console\Command;

abstract class BaseCommand extends Command
{
    private const NONE = 'none';

    protected function optionalChoice(string $question, array $choices, $default = null): string|false
    {
        $choice = $this->choice(
            question: $question,
            choices: array_merge($choices, [self::NONE]),
            default: $default,
        );
        return $choice === self::NONE ? false : $choice;
    }
}