<?php

namespace BlameButton\LaravelDockerBuilder\Objects;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;

class Configuration
{
    /**
     * @param  string[]  $phpExtensions
     */
    public function __construct(
        private readonly PhpVersion $phpVersion,
        private readonly array $phpExtensions,
        private readonly bool $artisanOptimize,
        private readonly bool $alpine,
        private readonly string|false $nodePackageManager,
        private readonly string|false $nodeBuildTool,
    ) {
    }

    public function getPhpVersion(): PhpVersion
    {
        return $this->phpVersion;
    }

    /**
     * @return string[]
     */
    public function getPhpExtensions(): array
    {
        return $this->phpExtensions;
    }

    public function isArtisanOptimize(): bool
    {
        return $this->artisanOptimize;
    }

    public function isAlpine(): bool
    {
        return $this->alpine;
    }

    public function getNodePackageManager(): string|false
    {
        return $this->nodePackageManager;
    }

    public function getNodeBuildTool(): string|false
    {
        return $this->nodeBuildTool;
    }

    /**
     * @return string[]
     */
    public function getCommand(): array
    {
        return array_values(array_filter([
            'php', 'artisan', 'docker:generate',
            '-n', // --no-interaction
            '-p '.$this->getPhpVersion()->label(), // --php-version
            '-e '.implode(',', $this->getPhpExtensions()), // --php-extensions
            $this->isArtisanOptimize() ? '-o' : null, // --optimize
            $this->isAlpine() ? '-a' : null, // --alpine
            $this->getNodePackageManager() ? '-m '.$this->getNodePackageManager() : null, // --node-package-manager
            $this->getNodePackageManager() ? '-b '.$this->getNodeBuildTool() : null, // --node-build-tool
        ]));
    }
}
