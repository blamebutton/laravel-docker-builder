<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

abstract class FileDetector implements DetectorContract
{
    public function detect(): string|false
    {
        foreach ($this->getPathMapping() as $file => $tool) {
            if (file_exists($file)) {
                return $tool;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    abstract protected function getPathMapping(): array;
}
