<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool;

class NodeBuildToolDetector extends FileDetector
{
    protected function getPathMapping(): array
    {
        return [
            base_path('vite.config.js') => NodeBuildTool::VITE,
            base_path('webpack.mix.js') => NodeBuildTool::MIX,
        ];
    }
}
