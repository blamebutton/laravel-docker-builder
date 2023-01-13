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
        $composer = json_decode($composer);
        $php = $composer?->require?->php;

        if (is_string($php)) {
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

        preg_match(
            pattern: '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/',
            subject: $php,
            matches: $matches,
        );

        if (empty($matches)) {
            return false;
        }

        return false;
    }
}
