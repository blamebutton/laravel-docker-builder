<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use Illuminate\Support\Str;

class CiPlatformDetector implements DetectorContract
{
    public function detect(): string|false
    {
        $config = base_path('.git/config');

        if (! file_exists($config)) {
            return false;
        }

        $config = file_get_contents($config);

        if (Str::contains($config, 'gitlab')) {
            return CiPlatform::GITLAB_CI;
        }

        if (Str::contains($config, 'github')) {
            return CiPlatform::GITHUB_ACTIONS;
        }

        return false;
    }
}
