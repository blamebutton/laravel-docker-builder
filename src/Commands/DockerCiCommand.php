<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use BlameButton\LaravelDockerBuilder\Detectors\CiPlatformDetector;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;

class DockerCiCommand extends BaseCommand
{
    protected $name = 'docker:ci';

    protected $description = 'Generate a CI file (supported: GitHub Actions, GitLab CI)';

    public function handle(): int
    {
        if ($argument = $this->argument('ci-platform')) {
            if (! in_array($argument, CiPlatform::values())) {
                $this->error("Invalid value [$argument] for argument [ci-platform].");

                return self::FAILURE;
            }

            return $this->copy($argument);
        }

        $detected = app(CiPlatformDetector::class)->detect();

        return self::SUCCESS;
    }

    protected function copy(string $platform): int
    {
        if (CiPlatform::GITLAB_CI === $platform) {
            $output = base_path('.gitlab-ci.yml');

            if (File::isFile($output)) {
                $this->info('Detected GitLab, but [.gitlab-ci.yml] file already exists.');

                return self::SUCCESS;
            }

            $this->info(sprintf('Detected GitLab, copying [.gitlab-ci.yml] to [%s].', dirname($output)));

            File::copy(package_path('resources/templates/.gitlab-ci.yml'), $output);
        }

        return self::SUCCESS;
    }

    protected function getArguments(): array
    {
        return [
            new InputArgument(
                name: 'ci-platform',
                mode: InputArgument::OPTIONAL,
                description: 'CI platform (supported: github, gitlab)',
            ),
        ];
    }
}
