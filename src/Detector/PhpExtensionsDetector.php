<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PhpExtensionsDetector implements DetectorContract
{
    private array $supported;

    public function supported(array $supported = null): self|array
    {
        if (is_null($supported)) {
            return $this->supported;
        }

        $this->supported = $supported;

        return $this;
    }

    public function detect(): string|false
    {
        $extensions = [
            $this->getDefaultExtensions(),
            $this->getCacheExtensions(),
            $this->getDatabaseExtensions(),
            $this->getBroadcastingExtensions(),
            $this->getQueueExtensions(),
            $this->getSessionExtensions(),
        ];

        return Collection::make($extensions)
            ->flatten()
            ->unique()
            ->sort()
            ->intersect($this->supported())
            ->map(fn (string $extension) => array_search($extension, $this->supported()))
            ->filter(fn ($value) => is_int($value))
            ->join(',');
    }

    /**
     * @return string[]
     */
    private function getDefaultExtensions(): array
    {
        return ['bcmath'];
    }

    /**
     * @return string[]
     */
    private function getCacheExtensions(): array
    {
        $store = config('cache.default');
        $driver = config("cache.stores.$store.driver");

        return Arr::wrap(match ($driver) {
            'apc' => 'apcu',
            'memcached' => 'memcached',
            'redis' => 'redis',
            default => [],
        });
    }

    /**
     * @return string[]
     */
    public function getDatabaseExtensions(): array
    {
        $connection = config('database.default');
        $driver = config("database.connections.$connection.driver");

        return Arr::wrap(match ($driver) {
            'mysql' => 'pdo_mysql',
            'pgsql' => 'pdo_pgsql',
            'sqlsrv' => ['pdo_sqlsrv', 'sqlsrv'],
            default => [],
        });
    }

    /**
     * @return string[]
     */
    public function getBroadcastingExtensions(): array
    {
        $connection = config('broadcasting.default');
        $driver = config("broadcasting.connections.$connection.driver");

        return Arr::wrap(match ($driver) {
            'redis' => 'redis',
            default => [],
        });
    }

    /**
     * @return string[]
     */
    public function getQueueExtensions(): array
    {
        $connection = config('queue.default');
        $driver = config("queue.connections.$connection.driver");

        return Arr::wrap(match ($driver) {
            'redis' => 'redis',
            default => [],
        });
    }

    /**
     * @return string[]
     */
    public function getSessionExtensions(): array
    {
        $store = config('session.driver');
        $driver = config("cache.stores.$store.driver");

        return Arr::wrap(match ($driver) {
            'apc' => 'apcu',
            'memcached' => 'memcached',
            'redis' => 'redis',
            default => [],
        });
    }
}
