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

        if ($detected) {
            return $this->copy($detected);
        }

        $this->warn('Unfortunately, no CI platform could be detected.');
        $this->warn('Please use the [ci-platform] argument to manually define a supported platform.');

        return self::FAILURE;
    }

    protected function copy(string $platform): int
    {
        if ($platform === CiPlatform::GITHUB_ACTIONS) {
            $template = package_path('resources/templates/ci-platforms/github-workflow.yml');
            $output = base_path('.github/workflows/ci.yml');
        } elseif ($platform === CiPlatform::GITLAB_CI) {
            $template = package_path('resources/templates/ci-platforms/.gitlab-ci.yml');
            $output = base_path('.gitlab-ci.yml');
        } else {
            $this->error('Invalid platform passed to '.__METHOD__.' this should never happen.');

            return self::INVALID;
        }

        $fromBasePath = str($output)->after(base_path().'/')->value();

        if (File::isFile($output)) {
            $this->info(sprintf(
                'Using [%s], but [%s] file already exists.',
                CiPlatform::name($platform),
                $fromBasePath,
            ));

            return self::SUCCESS;
        }

        $this->info(sprintf('Using [%s], copying [%s] to [%s].', CiPlatform::name($platform), $fromBasePath, dirname($output)));

        File::copy($template, $output);

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
