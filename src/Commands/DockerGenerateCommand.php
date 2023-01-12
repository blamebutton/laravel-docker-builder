<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use BlameButton\LaravelDockerBuilder\Commands\Choices\ArtisanOptimize;
use BlameButton\LaravelDockerBuilder\Commands\Choices\NodeBuildTool;
use BlameButton\LaravelDockerBuilder\Commands\Choices\NodePackageManager;
use BlameButton\LaravelDockerBuilder\Commands\Choices\PhpExtensions;
use BlameButton\LaravelDockerBuilder\Commands\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Detector\NodeBuildToolDetector;
use BlameButton\LaravelDockerBuilder\Detector\NodePackageManagerDetector;
use BlameButton\LaravelDockerBuilder\Detector\PhpExtensionsDetector;
use BlameButton\LaravelDockerBuilder\Detector\PhpVersionDetector;
use BlameButton\LaravelDockerBuilder\Traits\InteractsWithTwig;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

class DockerGenerateCommand extends BaseCommand
{
    use InteractsWithTwig;

    protected $name = 'docker:generate';

    protected $description = 'Generate Dockerfiles';

    public function handle(): int
    {
        $phpVersion = $this->getPhpVersion();
        $phpExtensions = $this->getPhpExtensions($phpVersion);
        $artisanOptimize = $this->getArtisanOptimize();
        $nodePackageManager = $this->getNodePackageManager();
        $nodeBuildTool = $nodePackageManager ? $this->getNodeBuildTool() : false;

        if ($this->option('detect')) {
            $this->info('Detected Configuration');
        }

        $this->table(['Key', 'Value'], [
            ['PHP version', "<comment>$phpVersion</comment>"],
            ['PHP extensions', implode(', ', $phpExtensions)],
            ['Artisan Optimize', '<comment>'.json_encode($artisanOptimize).'</comment>'],
            ['Node Package Manager', NodePackageManager::name($nodePackageManager)],
            ['Node Build Tool', $nodePackageManager ? NodeBuildTool::name($nodeBuildTool) : 'None'],
        ]);

        $dockerfiles = collect([
            'php.dockerfile' => $this->render('php.dockerfile.twig', [
                'php_version' => $phpVersion,
                'php_extensions' => $phpExtensions,
                'artisan_optimize' => $artisanOptimize,
                'node_package_manager' => $nodePackageManager,
                'node_build_tool' => $nodeBuildTool,
            ]),
            'nginx.dockerfile' => $this->render('nginx.dockerfile.twig', [
                'node_package_manager' => $nodePackageManager,
                'node_build_tool' => $nodeBuildTool,
            ]),
        ]);

        if (! is_dir($dir = base_path('.docker'))) {
            mkdir($dir);
        }

        foreach ($dockerfiles as $file => $content) {
            // Example: $PWD/.docker/{php,nginx}.dockerfile
            $dockerfile = sprintf('%s/%s', $dir, $file);

            // Save Dockerfile contents
            file_put_contents($dockerfile, $content);

            // Output saved Dockerfile location
            $filename = str($dockerfile)->after(base_path())->trim('/');
            $this->info(sprintf('Saved: %s', $filename));
        }

        return self::SUCCESS;
    }

    private function getPhpVersion(): string
    {
        if ($option = $this->option('php-version')) {
            return in_array($option, PhpVersion::values())
                ? $option
                : throw new \InvalidArgumentException("Invalid value [$option] for option [php-version]");
        }

        $detected = app(PhpVersionDetector::class)->detect();

        if ($this->option('detect')) {
            return $detected;
        }

        return $this->choice(
            question: 'PHP version',
            choices: PhpVersion::values(),
            default: $detected ?: PhpVersion::v8_2,
        );
    }

    private function getPhpExtensions(string $phpVersion): array
    {
        $supportedExtensions = PhpExtensions::values($phpVersion);

        if ($option = $this->option('php-extensions')) {
            return Collection::make(explode(',', $option))
                ->intersect($supportedExtensions)
                ->toArray();
        }

        $detected = app(PhpExtensionsDetector::class, ['supportedExtensions' => $supportedExtensions])->detect();

        if ($this->option('detect')) {
            $detected = explode(',', $detected);

            foreach ($detected as $key => $value) {
                $detected[$key] = $supportedExtensions[$value];
            }

            return $detected;
        }

        return $this->choice(
            question: 'PHP extensions',
            choices: $supportedExtensions,
            default: $detected,
            multiple: true,
        );
    }

    public function getArtisanOptimize(): bool
    {
        if ($this->option('optimize') || $this->option('detect')) {
            return true;
        }

        $choice = $this->choice(
            question: 'Do you want to run "php artisan optimize" when the image boots?',
            choices: ArtisanOptimize::values(),
            default: ArtisanOptimize::YES,
        );

        return ArtisanOptimize::YES === $choice;
    }

    private function getNodePackageManager(): string|false
    {
        if ($option = $this->option('node-package-manager')) {
            return in_array($option, NodePackageManager::values())
                ? $option
                : throw new \InvalidArgumentException("Invalid value [$option] for option [node-package-manager]");
        }

        $detected = app(NodePackageManagerDetector::class)->detect();

        if ($this->option('detect')) {
            return $detected;
        }

        return $this->optionalChoice(
            question: 'Which Node package manager do you use?',
            choices: NodePackageManager::values(),
            default: $detected ?: NodePackageManager::NPM,
        );
    }

    private function getNodeBuildTool(): string
    {
        if ($option = $this->option('node-build-tool')) {
            return in_array($option, NodeBuildTool::values())
                ? $option
                : throw new \InvalidArgumentException("Invalid value [$option] for option [node-build-tool]");
        }

        $detected = app(NodeBuildToolDetector::class)->detect();

        if ($this->option('detect')) {
            return $detected;
        }

        return $this->choice(
            question: 'Which Node build tool do you use?',
            choices: NodeBuildTool::values(),
            default: $detected ?: NodeBuildTool::VITE,
        );
    }

    protected function getOptions(): array
    {
        return [
            new InputOption(
                name: 'detect',
                shortcut: 'd',
                mode: InputOption::VALUE_NONE,
                description: 'Detect project requirements'
            ),
            new InputOption(
                name: 'php-version',
                shortcut: 'p',
                mode: InputOption::VALUE_REQUIRED,
                description: sprintf('PHP version (supported: %s)', implode(', ', PhpVersion::values())),
            ),
            new InputOption(
                name: 'php-extensions',
                shortcut: 'e',
                mode: InputOption::VALUE_REQUIRED,
                description: sprintf('PHP extensions (supported: %s)', implode(', ', PhpExtensions::values())),
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
                description: sprintf('Node Package Manager (supported: %s)', implode(', ', NodePackageManager::values())),
            ),
            new InputOption(
                name: 'node-build-tool',
                shortcut: 'b',
                mode: InputOption::VALUE_REQUIRED,
                description: sprintf('Node Build Tool (supported: %s)', implode(', ', NodeBuildTool::values())),
            ),
        ];
    }
}
