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
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Traits\InteractsWithTwig;
use Symfony\Component\Console\Input\InputOption;

class DockerGenerateCommand extends BaseCommand
{
    use InteractsWithTwig;

    protected $name = 'docker:generate';

    protected $description = 'Generate Dockerfiles';

    public function handle(): int
    {
        try {
            $phpVersion = $this->getPhpVersion();
            $phpExtensions = $this->getPhpExtensions($phpVersion);
            $artisanOptimize = $this->getArtisanOptimize();
            $nodePackageManager = $this->getNodePackageManager();
            $nodeBuildTool = $nodePackageManager ? $this->getNodeBuildTool() : false;
        } catch (InvalidOptionValueException $exception) {
            $this->error($exception->getMessage());

            return self::INVALID;
        }

        $this->info('Configuration:');
        $this->table(['Key', 'Value'], [
            ['PHP version', "<comment>$phpVersion</comment>"],
            ['PHP extensions', implode(', ', $phpExtensions)],
            ['Artisan Optimize', '<comment>'.json_encode($artisanOptimize).'</comment>'],
            ['Node Package Manager', NodePackageManager::name($nodePackageManager)],
            ['Node Build Tool', $nodePackageManager ? NodeBuildTool::name($nodeBuildTool) : 'None'],
        ]);
        $this->newLine();

        if (! $this->option('no-interaction') && ! $this->confirm('Does this look correct?', true)) {
            $this->comment('Exiting.');

            return self::SUCCESS;
        }

        $context = [
            'php_version' => $phpVersion,
            'php_extensions' => $phpExtensions,
            'artisan_optimize' => $artisanOptimize,
            'node_package_manager' => $nodePackageManager,
            'node_build_tool' => $nodeBuildTool,
        ];

        $this->saveDockerfiles($context);
        $this->newLine();

        $command = array_filter([
            'php',
            'artisan',
            'docker:generate',
            '-n', // --no-interaction
            '-p '.$phpVersion, // --php-version
            '-e '.implode(',', $phpExtensions), // --php-extensions
            $artisanOptimize ? '-o' : null, // --optimize
            $nodePackageManager ? '-m '.$nodePackageManager : null, // --node-package-manager
            $nodePackageManager ? '-b '.$nodeBuildTool : null, // --node-build-tool
        ]);

        $this->info('Command to generate above configuration:');
        $this->comment(sprintf('  %s', implode(' ', $command)));

        return self::SUCCESS;
    }

    /**
     * Get the PHP version, either by detecting it from the "composer.json",
     * from the "php-version" option, or asking the user.
     *
     * @return string
     *
     * @throws InvalidOptionValueException when an unsupported PHP version is passed
     */
    private function getPhpVersion(): string
    {
        if ($option = $this->option('php-version')) {
            return in_array($option, PhpVersion::values())
                ? $option
                : throw new InvalidOptionValueException("Invalid value [$option] for option [php-version]");
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

    /**
     * Get the PHP extensions, either by detecting them from the application's configuration,
     * from the "php-extensions" option, or asking the user.
     *
     * @param  string  $phpVersion
     * @return array
     *
     * @throws InvalidOptionValueException when an unsupported extension is passed
     */
    private function getPhpExtensions(string $phpVersion): array
    {
        $supportedExtensions = PhpExtensions::values($phpVersion);

        if ($option = $this->option('php-extensions')) {
            $extensions = explode(',', $option);

            foreach ($extensions as $extension) {
                if (in_array($extension, $supportedExtensions)) {
                    continue;
                }

                throw new InvalidOptionValueException("Extension [$extension] is not supported.");
            }

            return array_intersect($extensions, $supportedExtensions);
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

    /**
     * Get the Node Package Manager, either by detecting it from files present (package-lock.json, yarn.lock),
     * from the "node-package-manager" option, or asking the user.
     *
     * @return string|false
     *
     * @throws InvalidOptionValueException
     */
    private function getNodePackageManager(): string|false
    {
        if ($option = $this->option('node-package-manager')) {
            return in_array($option, NodePackageManager::values())
                ? $option
                : throw new InvalidOptionValueException("Invalid value [$option] for option [node-package-manager]");
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

    /**
     * Get the Node Build Tool, either by detecting it from files present (vite.config.js, webpack.mix.js),
     * from the "node-build-tool" option, or asking the user.
     *
     * @return string
     *
     * @throws InvalidOptionValueException
     */
    private function getNodeBuildTool(): string
    {
        if ($option = $this->option('node-build-tool')) {
            return in_array($option, NodeBuildTool::values())
                ? $option
                : throw new InvalidOptionValueException("Invalid value [$option] for option [node-build-tool]");
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

    private function saveDockerfiles(array $context): void
    {
        if (! is_dir($dir = base_path('.docker'))) {
            mkdir($dir);
        }

        $this->info('Saving Dockerfiles:');

        $dockerfiles = [
            'php.dockerfile' => $this->render('php.dockerfile.twig', $context),
            'nginx.dockerfile' => $this->render('nginx.dockerfile.twig', $context),
        ];

        foreach ($dockerfiles as $file => $content) {
            // Example: $PWD/.docker/{php,nginx}.dockerfile
            $dockerfile = sprintf('%s/%s', $dir, $file);

            // Save Dockerfile contents
            file_put_contents($dockerfile, $content);

            // Output saved Dockerfile location
            $filename = str($dockerfile)->after(base_path())->trim('/');
            $this->comment(sprintf('  Saved: %s', $filename));
        }
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
