[![Packagist Version](https://img.shields.io/packagist/v/blamebutton/laravel-docker-builder)](https://packagist.org/packages/blamebutton/laravel-docker-builder)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/blamebutton/laravel-docker-builder/phpunit.yml)

# Laravel Docker Build

## Installation

```shell
composer require --dev blamebutton/laravel-docker-builder
```

## Configuration

### Option 1: Config File

```shell
php artisan vendor:publish --provider="BlameButton\LaravelDockerBuilder\DockerServiceProvider"
```

### Option 2: `.env`

By default, the configuration file reads the following environment variables to determine the Docker image tags.

```shell
DOCKER_NGINX_TAG=laravel-app:nginx
DOCKER_PHP_TAG=laravel-app:php
```

## Usage

```shell
php artisan docker:build
```
