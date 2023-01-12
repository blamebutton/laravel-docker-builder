<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

use BlameButton\LaravelDockerBuilder\Commands\Choices\NodeBuildTool;

class NodeBuildToolDetector extends FileDetector
{
    protected function getPathMapping(): array
    {
        return [
            base_path('webpack.mix.js') => NodeBuildTool::MIX,
            base_path('vite.config.js') => NodeBuildTool::VITE,
        ];
    }
}
