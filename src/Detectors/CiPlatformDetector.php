<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CiPlatformDetector implements DetectorContract
{
    public function detect(): string|false
    {
        $config = base_path('.git/config');

        if (File::missing($config)) {
            return false;
        }

        $config = File::get($config);

        if (Str::contains($config, 'gitlab')) {
            return CiPlatform::GITLAB_CI;
        }

        if (Str::contains($config, 'github')) {
            return CiPlatform::GITHUB_ACTIONS;
        }

        return false;
    }
}
