<?php

namespace BlameButton\LaravelDockerBuilder\Detector;

interface DetectorContract
{
    public function detect(): string|false;
}
