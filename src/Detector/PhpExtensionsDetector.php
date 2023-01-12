<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PhpExtensionsDetector implements DetectorContract
{
    public function __construct(private array $supportedExtensions)
    {
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
            ->intersect($this->supportedExtensions)
            ->map(fn (string $extension) => array_search($extension, $this->supportedExtensions))
            ->filter()
            ->join(',');
    }

    private function getDefaultExtensions(): array
    {
        return ['bcmath'];
    }

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
     * @return array
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
     * @return array
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
