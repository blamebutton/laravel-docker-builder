<?php

namespace BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions;
use BlameButton\LaravelDockerBuilder\Detector\PhpExtensionsDetector;
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
        $supportedExtensions = PhpExtensions::values($phpVersion);

        if ($option = $command->option('php-extensions')) {
            $extensions = explode(',', $option);

            foreach ($extensions as $extension) {
                if (in_array($extension, $supportedExtensions)) {
                    continue;
                }

                throw new InvalidOptionValueException("Extension [$extension] is not supported.");
            }

            return array_intersect($extensions, $supportedExtensions);
        }

        $detected = app(PhpExtensionsDetector::class, ['supportedExtensions' => $supportedExtensions])->detect();

        if ($command->option('detect')) {
            $detected = explode(',', $detected);

            foreach ($detected as $key => $value) {
                $detected[$key] = $supportedExtensions[$value];
            }

            return $detected;
        }

        return $command->choice(
            question: 'PHP extensions',
            choices: $supportedExtensions,
            default: $detected,
            multiple: true,
        );
    }
}
