<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

interface DetectorContract
{
    public function detect(): array|string|false;
}
