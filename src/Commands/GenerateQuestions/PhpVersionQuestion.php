<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Detector\PhpVersionDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;

class PhpVersionQuestion extends BaseQuestion
{
    /**
     * Get the PHP version, either by detecting it from the "composer.json",
     * from the "php-version" option, or asking the user.
     *
     * @param  BaseCommand  $command
     * @return string
     *
     * @throws InvalidOptionValueException when an unsupported PHP version is passed
     */
    public function getAnswer(BaseCommand $command): string
    {
        if ($option = $command->option('php-version')) {
            return in_array($option, PhpVersion::values())
                ? $option
                : throw new InvalidOptionValueException("Invalid value [$option] for option [php-version]");
        }

        $detected = app(PhpVersionDetector::class)->detect();

        if ($command->option('detect')) {
            return $detected;
        }

        return $command->choice(
            question: 'PHP version',
            choices: PhpVersion::values(),
            default: $detected ?: PhpVersion::v8_2,
        );
    }
}