<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use Illuminate\Support\Str;

class CiPlatformDetector implements DetectorContract
{
    public function detect(): string|false
    {
        $base = base_path();

        do {
            $git = $base.'/.git/config';

            if (! file_exists($git)) {
                $base = Str::beforeLast($base, '/');

                continue;
            }

            $git = file_get_contents($git);

            if (Str::contains($git, 'gitlab')) {
                return CiPlatform::GITLAB_CI;
            }

            if (Str::contains($git, 'github')) {
                return CiPlatform::GITHUB_ACTIONS;
            }
        } while (Str::contains($base, '/'));

        return false;
    }
}
