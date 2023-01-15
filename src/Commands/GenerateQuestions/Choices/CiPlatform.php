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

    public static function name(string $value): string
    {
        return match ($value) {
            self::GITHUB_ACTIONS => 'GitHub Actions',
            self::GITLAB_CI => 'GitLab CI/CD',
        };
    }
}
