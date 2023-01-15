<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool;
use BlameButton\LaravelDockerBuilder\Detectors\NodeBuildToolDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;

class NodeBuildToolQuestion extends BaseQuestion
{
    /**
     * Get the Node Build Tool, either by detecting it from files present (vite.config.js, webpack.mix.js),
     * from the "node-build-tool" option, or asking the user.
     *
     * @param  BaseCommand  $command
     * @return string
     *
     * @throws InvalidOptionValueException
     */
    public function getAnswer(BaseCommand $command): string
    {
        if ($option = $command->option('node-build-tool')) {
            return in_array($option, NodeBuildTool::values())
                ? $option
                : throw new InvalidOptionValueException("Invalid value [$option] for option [node-build-tool]");
        }

        $detected = app(NodeBuildToolDetector::class)->detect();

        if ($command->option('detect')) {
            return $detected;
        }

        return $command->choice(
            question: 'Which Node build tool do you use?',
            choices: NodeBuildTool::values(),
            default: $detected ?: NodeBuildTool::VITE,
        );
    }
}
