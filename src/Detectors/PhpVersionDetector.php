<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use Composer\Semver\VersionParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PhpVersionDetector implements DetectorContract
{
    public function detect(): string|false
    {
        $composer = $this->getComposerFileContents();
        if ($composer === false) {
            return false;
        }

        $composer = json_decode($composer);
        $php = data_get($composer, 'require.php');
        if (! is_string($php)) {
            return false;
        }

        $php = app(VersionParser::class)
            ->parseConstraints($php)
            ->getLowerBound()
            ->getVersion();

        return Arr::first(
            array: PhpVersion::values(),
            callback: fn ($value) => Str::startsWith($php, $value),
            default: false,
        );
    }

    public function getComposerFileContents(): string|false
    {
        if (File::missing($path = base_path('composer.json'))) {
            return false;
        }

        return File::get($path);
    }
}
