<?php

use Illuminate\Support\Str;

$tag = Str::slug(env('APP_NAME', 'laravel'));

return [
    'tags' => [
        'nginx' => env('DOCKER_NGINX_TAG', "$tag:nginx"),
        'php' => env('DOCKER_PHP_TAG', "$tag:php"),
    ],
];