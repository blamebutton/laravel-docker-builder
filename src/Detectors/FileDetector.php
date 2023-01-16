<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

use Illuminate\Support\Facades\File;

abstract class FileDetector implements DetectorContract
{
    public function detect(): string|false
    {
        foreach ($this->getPathMapping() as $file => $tool) {
            if (File::isFile($file)) {
                return $tool;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    abstract public function getPathMapping(): array;
}
