<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\AlpineQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\ArtisanOptimizeQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodeBuildToolQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodePackageManagerQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpExtensionsQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpVersionQuestion;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Objects\Configuration;
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
            $config = new Configuration(
                phpVersion: $phpVersion = app(PhpVersionQuestion::class)->getAnswer($this),
                phpExtensions: app(PhpExtensionsQuestion::class)->getAnswer($this, $phpVersion),
                artisanOptimize: app(ArtisanOptimizeQuestion::class)->getAnswer($this),
                alpine: app(AlpineQuestion::class)->getAnswer($this),
                nodePackageManager: $nodePackageManager = app(NodePackageManagerQuestion::class)->getAnswer($this),
                nodeBuildTool: $nodePackageManager ? app(NodeBuildToolQuestion::class)->getAnswer($this) : false,
            );
        } catch (InvalidOptionValueException $exception) {
            $this->error($exception->getMessage());

            return self::INVALID;
        }

        $this->printConfigurationTable($config);
        $this->newLine();

        if (! $this->option('no-interaction') && ! $this->confirm('Does this look correct?', true)) {
            $this->comment('Exiting.');

            return self::SUCCESS;
        }

        $this->saveDockerfiles($config);
        $this->newLine();

        $this->info('Command to generate above configuration:');
        $this->comment(sprintf('  %s', implode(' ', $config->getCommand())));

        return self::SUCCESS;
    }

    public function printConfigurationTable(Configuration $config): void
    {
        $this->info('Configuration:');

        $this->table(['Key', 'Value'], [
            ['PHP version',
                '<comment>'.$config->getPhpVersion().'</comment>',
            ],
            ['PHP extensions',
                implode(', ', $config->getPhpExtensions()),
            ],
            ['Artisan Optimize',
                '<comment>'.json_encode($config->isArtisanOptimize()).'</comment>',
            ],
            ['Alpine images',
                '<comment>'.json_encode($config->isAlpine()).'</comment>',
            ],
            ['Node package manager',
                NodePackageManager::name($config->getNodePackageManager()),
            ],
            ['Node build tool',
                $config->getNodePackageManager()
                    ? NodeBuildTool::name($config->getNodeBuildTool())
                    : 'None',
            ],
        ]);
    }

    private function saveDockerfiles(Configuration $config): void
    {
        if (! is_dir($dir = base_path('.docker'))) {
            mkdir($dir);
        }

        $this->info('Saving Dockerfiles:');

        $context = [
            'php_version' => $config->getPhpVersion(),
            'php_extensions' => implode(' ', $config->getPhpExtensions()),
            'artisan_optimize' => $config->isArtisanOptimize(),
            'alpine' => $config->isAlpine(),
            'node_package_manager' => $config->getNodePackageManager(),
            'node_build_tool' => $config->getNodeBuildTool(),
        ];

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
                mode: InputOption::VALUE_NEGATABLE,
                description: 'Add "php artisan optimize" to entrypoint',
            ),
            new InputOption( // TODO: implement opcache extension
                name: 'opcache',
                mode: InputOption::VALUE_NEGATABLE,
                description: 'Add "opcache" extension and configure it',
                default: true,
            ),
            new InputOption(
                name: 'alpine',
                shortcut: 'a',
                mode: InputOption::VALUE_NEGATABLE,
                description: 'Use Alpine Linux based images',
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
