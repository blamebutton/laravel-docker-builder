# Laravel Docker Build

## Installation

```shell
composer require --dev blamebutton/laravel-docker-builder
```

## Configuration

Two environment variables need to be set:

* `DOCKER_NGINX_TAG`
* `DOCKER_PHP_TAG`

This can be done either by adding them to your `.env` file or passing them to the build command.

### Option 1: `.env`

```
DOCKER_NGINX_TAG=laravel-app:nginx
DOCKER_PHP_TAG=laravel-app:php
```

### Option 2: CLI

```shell
DOCKER_NGINX_TAG=laravel-app:nginx DOCKER_PHP_TAG=laravel-app:php vendor/bin/docker-build
```

### Option 3: Config File

```shell
php artisan vendor:publish --provider="BlameButton\LaravelDockerBuilder\DockerServiceProvider"
```

## Usage

Set the `DOCKER_NGINX_TAG` and `DOCKER_PHP_TAG` environment variables and run:

```shell
vendor/bin/docker-build
```
