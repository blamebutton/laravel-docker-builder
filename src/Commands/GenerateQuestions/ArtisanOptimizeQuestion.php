<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\ArtisanOptimize;

class ArtisanOptimizeQuestion extends BaseQuestion
{
    public function getAnswer(BaseCommand $command): bool
    {
        if ($command->option('optimize') || $command->option('detect')) {
            return true;
        }

        $choice = $command->choice(
            question: 'Do you want to run "php artisan optimize" when the image boots?',
            choices: ArtisanOptimize::values(),
            default: ArtisanOptimize::YES,
        );

        return ArtisanOptimize::YES === $choice;
    }
}
