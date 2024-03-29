<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Detectors\PhpExtensionsDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;

class PhpExtensionsQuestion extends BaseQuestion
{
    public function __construct(
        private readonly PhpExtensionsDetector $phpExtensionsDetector,
    ) {
    }

    /**
     * Get the PHP extensions, either by detecting them from the application's configuration,
     * from the "php-extensions" option, or asking the user.
     *
     * @throws InvalidOptionValueException when an unsupported extension is passed
     */
    public function getAnswer(BaseCommand $command, PhpVersion $phpVersion): array
    {
        $supported = PhpExtensions::values($phpVersion);

        if ($option = $command->option('php-extensions')) {
            $extensions = explode(',', $option);

            foreach ($extensions as $extension) {
                if (in_array($extension, $supported)) {
                    continue;
                }

                throw new InvalidOptionValueException("Extension [$extension] is not supported.");
            }

            return array_intersect($extensions, $supported);
        }

        $detected = $this->phpExtensionsDetector
            ->supported($supported)
            ->detect();

        if ($command->option('detect')) {
            return $detected;
        }

        $default = collect($detected)
            ->map(fn ($extension) => array_search($extension, $supported))
            ->where(fn ($key) => is_int($key))
            ->join(',');

        return $command->choice(
            question: 'PHP extensions',
            choices: $supported,
            default: $default,
            multiple: true,
        );
    }
}
