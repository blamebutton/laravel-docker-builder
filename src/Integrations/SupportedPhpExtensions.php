<?php

namespace BlameButton\LaravelDockerBuilder\Integrations;

class SupportedPhpExtensions
{
    private const URL = 'https://github.com/mlocati/docker-php-extension-installer/raw/master/data/supported-extensions';

    private array|null $cache = null;

    public function get(string $phpVersion = null): array
    {
        if (! is_null($this->cache)) {
            return $this->cache;
        }

        $contents = $this->fetch();

        if ($contents === false) {
            return [];
        }

        return $this->cache = collect($contents)
            ->filter(fn (string $extension): bool => is_null($phpVersion) || str($extension)->contains($phpVersion))
            ->map(fn (string $extension): string => str($extension)->trim()->before(' '))
            ->filter()
            ->values()
            ->toArray();
    }

    protected function fetch(): array|false
    {
        return file(self::URL);
    }
}
