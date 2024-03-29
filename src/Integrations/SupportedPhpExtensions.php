<?php

namespace BlameButton\LaravelDockerBuilder\Integrations;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use Illuminate\Support\Facades\Http;

class SupportedPhpExtensions
{
    private const URL = 'https://github.com/mlocati/docker-php-extension-installer/raw/master/data/supported-extensions';

    private ?array $cache = null;

    public function get(?PhpVersion $phpVersion = null): array
    {
        if (! is_null($this->cache)) {
            return $this->cache;
        }

        $contents = $this->fetch();

        if ($contents === false) {
            return [];
        }

        return $this->cache = collect($contents)
            ->filter(fn (string $extension): bool => is_null($phpVersion) || str($extension)->contains($phpVersion->label()))
            ->map(fn (string $extension): string => str($extension)->trim()->before(' '))
            ->filter()
            ->values()
            ->toArray();
    }

    public function fetch(): array|false
    {
        $response = rescue(
            callback: fn () => Http::get(self::URL),
            rescue: false,
        );

        if ($response === false || $response->failed()) {
            return false;
        }

        return array_filter(explode("\n", $response->body()));
    }
}
