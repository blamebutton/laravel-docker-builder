<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;

class AlpineQuestion extends BaseQuestion
{
    public function getAnswer(BaseCommand $command): bool
    {
        if ($command->option('alpine') === false) {
            return false;
        }

        if ($command->option('alpine') || $command->option('detect')) {
            return true;
        }

        return $command->confirm(
            question: 'Do you want to use "Alpine Linux" based images?',
            default: true
        );
    }
}
