<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use BlameButton\LaravelDockerBuilder\Commands\Choices\NodeBuildTool;
use BlameButton\LaravelDockerBuilder\Commands\Choices\NodePackageManager;
use BlameButton\LaravelDockerBuilder\Commands\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Traits\InteractsWithTwig;
use Symfony\Component\Console\Input\InputOption;

class DockerGenerateCommand extends BaseCommand
{
    use InteractsWithTwig;

    const YES = 'yes';
    const NO = 'no';
    const NONE = 'none';

    protected $name = 'docker:generate';

    protected $description = 'Generate Dockerfiles';

    public function handle(): int
    {
        $phpVersion = $this->getPhpVersion();
        $artisanOptimize = $this->getArtisanOptimize();

        $nodePackageManager = $this->getNodePackageManager();
        $nodeBuildTool = $nodePackageManager ? $this->getNodeBuildTool() : false;

        $dockerfiles = collect([
            'php.dockerfile' => $this->render('php.dockerfile.twig', [
                'php_version' => $phpVersion,
                'artisan_optimize' => $artisanOptimize,
                'node_package_manager' => $nodePackageManager,
                'node_build_tool' => $nodeBuildTool,
            ]),
            'nginx.dockerfile' => $this->render('nginx.dockerfile.twig', [
                'node_package_manager' => $nodePackageManager,
                'node_build_tool' => $nodeBuildTool,
            ]),
        ]);

        $dockerfiles->each(function ($value, $key) {
            $this->info($key);
        });

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {

        return [
            new InputOption(
                name: 'php-version',
                shortcut: 'p',
                mode: InputOption::VALUE_REQUIRED,
                description: sprintf("PHP version (supported: %s)", join(', ', PhpVersion::values())),
            ),
            new InputOption(
                name: 'optimize',
                shortcut: 'o',
                mode: InputOption::VALUE_NONE,
                description: 'Add "php artisan optimize" to entrypoint',
            ),
            new InputOption(
                name: 'node-package-manager',
                shortcut: 'm',
                mode: InputOption::VALUE_REQUIRED,
                description: sprintf('Node Package Manager (supported: %s)', join(', ', NodePackageManager::values())),
            ),
            new InputOption(
                name: 'node-build-tool',
                shortcut: 'b',
                mode: InputOption::VALUE_REQUIRED,
                description: sprintf('Node Build Tool (supported: %s)', join(', ', NodeBuildTool::values())),
            ),
        ];
    }

    private function getPhpVersion(): string
    {
        if ($option = $this->option('php-version')) {
            return in_array($option, PhpVersion::values())
                ? $option
                : throw new \InvalidArgumentException("Invalid value [$option] for option [php-version]");
        }

        return $this->choice(
            question: 'PHP version',
            choices: PhpVersion::values(),
            default: PhpVersion::v8_2,
        );
    }

    public function getArtisanOptimize(): bool
    {
        if ($this->option('optimize')) {
            return true;
        }

        $choice = $this->choice(
            question: 'Do you want to run "php artisan optimize" when the image boots?',
            choices: [self::YES, self::NO],
            default: self::YES,
        );
        return $choice === self::YES;
    }

    private function getNodePackageManager(): string|false
    {
        if ($option = $this->option('node-package-manager')) {
            return in_array($option, NodePackageManager::values())
                ? $option
                : throw new \InvalidArgumentException("Invalid value [$option] for option [node-package-manager]");
        }

        return $this->optionalChoice(
            question: 'Which Node package manager do you use?',
            choices: NodePackageManager::values(),
            default: NodePackageManager::NPM,
        );
    }


    private function getNodeBuildTool(): string
    {
        if ($option = $this->option('node-build-tool')) {
            return in_array($option, NodeBuildTool::values())
                ? $option
                : throw new \InvalidArgumentException("Invalid value [$option] for option [node-build-tool]");
        }

        return $this->choice(
            question: 'Which Node build tool do you use?',
            choices: NodeBuildTool::values(),
            default: NodeBuildTool::VITE,
        );
    }
}