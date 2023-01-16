<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool;

class NodeBuildToolDetector extends FileDetector
{
    public function getPathMapping(): array
    {
        return [
            base_path('vite.config.js') => NodeBuildTool::VITE,
            base_path('vite.config.ts') => NodeBuildTool::VITE,
            base_path('webpack.mix.js') => NodeBuildTool::MIX,
        ];
    }
}
