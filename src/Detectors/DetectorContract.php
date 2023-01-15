<?php

namespace BlameButton\LaravelDockerBuilder\Detectors;

interface DetectorContract
{
    public function detect(): string|false;
}
