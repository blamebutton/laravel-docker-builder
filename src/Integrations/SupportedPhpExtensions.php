<?php

namespace BlameButton\LaravelDockerBuilder\Integrations;

class SupportedPhpExtensions
{
    private const URL = 'https://github.com/mlocati/docker-php-extension-installer/raw/master/data/supported-extensions';

    private array|null $cache = null;

    public function fetch(string $phpVersion = null): array
    {
        if (! is_null($this->cache)) {
            return $this->cache;
        }

        try {
            $contents = file_get_contents(self::URL);

            return $this->cache = str($contents)
                ->explode("\n")
                ->filter(fn (string $extension): bool => is_null($phpVersion) || str($extension)->contains($phpVersion))
                ->map(fn (string $extension): string => str($extension)->trim()->before(' '))
                ->filter()
                ->toArray();
        } catch (\ErrorException) {
            return [];
        }
    }
}
