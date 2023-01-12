<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

use BlameButton\LaravelDockerBuilder\Commands\Choices\NodePackageManager;

class NodePackageManagerDetector extends FileDetector
{
    protected function getPathMapping(): array
    {
        return [
            base_path('package-lock.json') => NodePackageManager::NPM,
            base_path('yarn.lock') => NodePackageManager::YARN,
        ];
    }
}
