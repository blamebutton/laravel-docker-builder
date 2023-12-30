<?php

if (! function_exists('package_path')) {
    function package_path(?string $path = null): string
    {
        $dir = dirname(__FILE__, 2);

        if (! is_null($path) && $path = ltrim($path, DIRECTORY_SEPARATOR)) {
            return $dir.DIRECTORY_SEPARATOR.$path;
        }

        return $dir;
    }
}
