<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager;
use BlameButton\LaravelDockerBuilder\Detectors\NodePackageManagerDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;

class NodePackageManagerQuestion extends BaseQuestion
{
    /**
     * Get the Node Package Manager, either by detecting it from files present (package-lock.json, yarn.lock),
     * from the "node-package-manager" option, or asking the user.
     *
     *
     * @throws InvalidOptionValueException
     */
    public function getAnswer(BaseCommand $command): string|false
    {
        if ($option = $command->option('node-package-manager')) {
            return in_array($option, NodePackageManager::values())
                ? $option
                : throw new InvalidOptionValueException("Invalid value [$option] for option [node-package-manager].");
        }

        $detected = app(NodePackageManagerDetector::class)->detect();

        if ($detected && $command->option('detect')) {
            return $detected;
        }

        return $command->optionalChoice(
            question: 'Which Node package manager do you use?',
            choices: NodePackageManager::values(),
            default: $detected ?: NodePackageManager::NPM,
        );
    }
}
