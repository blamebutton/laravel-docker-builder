<?php

namespace BlameButton\LaravelDockerBuilder\Objects;

class Configuration
{
    /**
     * @param  string  $phpVersion
     * @param  string[]  $phpExtensions
     * @param  bool  $artisanOptimize
     * @param  bool  $alpine
     * @param  string|false  $nodePackageManager
     * @param  string|false  $nodeBuildTool
     */
    public function __construct(
        private string $phpVersion,
        private array $phpExtensions,
        private bool $artisanOptimize,
        private bool $alpine,
        private string|false $nodePackageManager,
        private string|false $nodeBuildTool,
    ) {
    }

    public function getPhpVersion(): string
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
            '-p '.$this->getPhpVersion(), // --php-version
            '-e '.implode(',', $this->getPhpExtensions()), // --php-extensions
            $this->isArtisanOptimize() ? '-o' : null, // --optimize
            $this->isAlpine() ? '-a' : null, // --alpine
            $this->getNodePackageManager() ? '-m '.$this->getNodePackageManager() : null, // --node-package-manager
            $this->getNodePackageManager() ? '-b '.$this->getNodeBuildTool() : null, // --node-build-tool
        ]));
    }
}
