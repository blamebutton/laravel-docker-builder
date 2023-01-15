<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;

class ArtisanOptimizeQuestion extends BaseQuestion
{
    public function getAnswer(BaseCommand $command): bool
    {
        if ($command->option('optimize') === false) {
            return false;
        }

        if ($command->option('optimize') || $command->option('detect')) {
            return true;
        }

        return $command->confirm(
            question: 'Do you want to run "php artisan optimize" when the image boots?',
            default: true,
        );
    }
}
