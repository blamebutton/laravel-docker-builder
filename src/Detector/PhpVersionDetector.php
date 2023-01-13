<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use Composer\Semver\VersionParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PhpVersionDetector implements DetectorContract
{
    public function detect(): string|false
    {
        $composer = file_get_contents(base_path('composer.json'));
        if (! $composer) {
            return false;
        }

        $composer = json_decode($composer);
        $php = data_get($composer, 'require.php');

        if (! is_string($php)) {
            return false;
        }

        $parser = new VersionParser();
        $php = $parser->parseConstraints($php)
            ->getLowerBound()
            ->getVersion();

        return Arr::first(
            array: PhpVersion::values(),
            callback: fn ($value) => Str::startsWith($php, $value),
            default: false,
        );
    }
}
