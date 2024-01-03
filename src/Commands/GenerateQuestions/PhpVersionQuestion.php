<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Detectors\PhpVersionDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;

class PhpVersionQuestion extends BaseQuestion
{
    public function __construct(
        private readonly PhpVersionDetector $phpVersionDetector,
    ) {
    }

    /**
     * Get the PHP version, either by detecting it from the "composer.json",
     * from the "php-version" option, or asking the user.
     *
     * @throws InvalidOptionValueException when an unsupported PHP version is passed
     */
    public function getAnswer(BaseCommand $command): string
    {
        if ($option = $command->option('php-version')) {
            return in_array($option, PhpVersion::values())
                ? $option
                : throw new InvalidOptionValueException("Invalid value [$option] for option [php-version].");
        }

        $detected = $this->phpVersionDetector->detect();

        if ($detected !== false && $command->option('detect')) {
            return PhpVersion::from($detected)->label();
        }

        return $command->choice(
            question: 'PHP version',
            choices: PhpVersion::values(),
            default: $detected ?: PhpVersion::v8_3->label(),
        );
    }
}
