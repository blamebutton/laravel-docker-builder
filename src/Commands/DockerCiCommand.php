<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use BlameButton\LaravelDockerBuilder\Detector\CiPlatformDetector;

class DockerCiCommand extends BaseCommand
{
    protected $name = 'docker:ci';

    protected $description = '';

    public function handle(): int
    {
        $detected = app(CiPlatformDetector::class)->detect();

        if (CiPlatform::GITLAB_CI === $detected) {
            $output = base_path('.gitlab-ci.yml');

            if (file_exists($output)) {
                return self::SUCCESS;
            }

            copy(package_path('resources/templates/.gitlab-ci.yml'), $output);
        }

        return self::SUCCESS;
    }
}
