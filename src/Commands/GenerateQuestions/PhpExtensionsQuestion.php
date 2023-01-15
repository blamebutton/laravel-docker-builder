<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions;
use BlameButton\LaravelDockerBuilder\Detectors\PhpExtensionsDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;

class PhpExtensionsQuestion extends BaseQuestion
{
    /**
     * Get the PHP extensions, either by detecting them from the application's configuration,
     * from the "php-extensions" option, or asking the user.
     *
     * @param  BaseCommand  $command
     * @param  string  $phpVersion
     * @return array
     *
     * @throws InvalidOptionValueException when an unsupported extension is passed
     */
    public function getAnswer(BaseCommand $command, string $phpVersion): array
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

        $detected = app(PhpExtensionsDetector::class)
            ->supported($supported)
            ->detect();

        if ($command->option('detect')) {
            $detected = explode(',', $detected);

            foreach ($detected as $key => $value) {
                $detected[$key] = $supported[$value];
            }

            return $detected;
        }

        return $command->choice(
            question: 'PHP extensions',
            choices: $supported,
            default: $detected,
            multiple: true,
        );
    }
}
