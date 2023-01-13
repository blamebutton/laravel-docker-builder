<?php

namespace BlameButton\LaravelDockerBuilder\Objects;

class Configuration
{
    public function __construct(
        private string $phpVersion,
        private array $phpExtensions,
        private bool $artisanOptimize,
        private string|false $nodePackageManager,
        private string|false $nodeBuildTool,
    ) {
    }

    public function getPhpVersion(): string
    {
        return $this->phpVersion;
    }

    public function getPhpExtensions(): array
    {
        return $this->phpExtensions;
    }

    public function isArtisanOptimize(): bool
    {
        return $this->artisanOptimize;
    }

    public function getNodePackageManager(): string|false
    {
        return $this->nodePackageManager;
    }

    public function getNodeBuildTool(): string|false
    {
        return $this->nodeBuildTool;
    }

    public function getCommand(): array
    {
        return array_filter([
            'php', 'artisan', 'docker:generate',
            '-n', // --no-interaction
            '-p '.$this->getPhpVersion(), // --php-version
            '-e '.implode(',', $this->getPhpExtensions()), // --php-extensions
            $this->isArtisanOptimize() ? '-o' : null, // --optimize
            $this->getNodePackageManager() ? '-m '.$this->getNodePackageManager() : null, // --node-package-manager
            $this->getNodePackageManager() ? '-b '.$this->getNodeBuildTool() : null, // --node-build-tool
        ]);
    }
}
