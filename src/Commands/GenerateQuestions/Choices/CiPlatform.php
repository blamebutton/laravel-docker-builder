<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices;

class CiPlatform
{
    public const GITHUB_ACTIONS = 'github';

    public const GITLAB_CI = 'gitlab';

    public static function values(): array
    {
        return [
            self::GITHUB_ACTIONS,
            self::GITLAB_CI,
        ];
    }
}
