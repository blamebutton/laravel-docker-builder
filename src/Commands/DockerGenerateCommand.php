<?php

namespace BlameButton\LaravelDockerBuilder\Commands;

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
            $phpVersion = app(PhpVersionQuestion::class)->getAnswer($this);
            $phpExtensions = app(PhpExtensionsQuestion::class)->getAnswer($this, $phpVersion);
            $artisanOptimize = app(ArtisanOptimizeQuestion::class)->getAnswer($this);
            $nodePackageManager = app(NodePackageManagerQuestion::class)->getAnswer($this);
            $nodeBuildTool = $nodePackageManager ? app(NodeBuildToolQuestion::class)->getAnswer($this) : false;
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

        $this->saveDockerfiles([
            'php_version' => $phpVersion,
            'php_extensions' => implode(' ', $phpExtensions),
            'artisan_optimize' => $artisanOptimize,
            'node_package_manager' => $nodePackageManager,
            'node_build_tool' => $nodeBuildTool,
        ]);
        $this->newLine();

        $command = array_filter([
            'php', 'artisan', 'docker:generate',
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
                mode: InputOption::VALUE_NEGATABLE,
                description: 'Add "php artisan optimize" to entrypoint',
            ),
            new InputOption(
                name: 'opcache',
                mode: InputOption::VALUE_NEGATABLE,
                description: 'Add "opcache" extension and configure it',
                default: true,
            ),
            new InputOption(
                name: 'alpine',
                mode: InputOption::VALUE_NEGATABLE,
                description: 'Use Alpine Linux based images',
                default: true,
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
