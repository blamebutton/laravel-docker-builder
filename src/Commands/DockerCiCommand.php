<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use BlameButton\LaravelDockerBuilder\Detectors\CiPlatformDetector;

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
                $this->info('Detected GitLab, but [.gitlab-ci.yml] file already exists.');

                return self::SUCCESS;
            }

            $this->info(sprintf('Detected GitLab, copying [.gitlab-ci.yml] to [%s].', dirname($output)));

            copy(package_path('resources/templates/.gitlab-ci.yml'), $output);
        }

        return self::SUCCESS;
    }
}
