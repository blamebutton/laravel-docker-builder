<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager;

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
