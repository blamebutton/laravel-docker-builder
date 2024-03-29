![Banner](https://banners.beyondco.de/Laravel%20Docker%20Builder.png?theme=light&packageManager=composer+require&packageName=blamebutton%2Flaravel-docker-builder&pattern=architect&style=style_1&description=Create+Dockerfiles+and+Kubernetes+manifests+for+your+application&md=1&showWatermark=1&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg&widths=auto)

[![Packagist Version](https://img.shields.io/packagist/v/blamebutton/laravel-docker-builder)](https://packagist.org/packages/blamebutton/laravel-docker-builder)
[![Packagist Downloads](https://img.shields.io/packagist/dm/blamebutton/laravel-docker-builder)](https://packagist.org/packages/blamebutton/laravel-docker-builder)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/blamebutton/laravel-docker-builder/phpunit.yml)

## Features

* Build Docker images using the Artisan CLI
* Detect PHP version and extensions
* Bundle assets with Vite.js or Laravel Mix
* Separate NGINX and PHP-FPM images
* [Deployment examples](/.examples/) for Kubernetes and Docker Compose

## Installation

```shell
composer require --dev blamebutton/laravel-docker-builder
```

## Usage

### Detect Configuration

```shell
php artisan docker:generate --detect
```

When `--detect` is passed to the `docker:generate` command, it will automatically detect the following requirements:

* PHP version, detected using the `php` version in your `composer.json`
* PHP extensions, detected using the configuration of your project:
    * Cache driver: Redis, Memcached, APC
    * Database driver: MySQL, Postgres, SQL Server
    * Broadcasting driver: Redis
    * Queue driver: Redis
    * Session driver: Redis, Memcached, APC
* Node package manager, detected using the existence of `package-lock.json` or `yarn.lock`
* Node build tool, detected using the existence of `vite.config.js` or `webpack.mix.js`

### Manual Configuration

```shell
php artisan docker:generate
```

When no options are passed to `docker:generate`, a prompt is used to configure the project's requirements.

See all available options, and their supported values, by running `php artisan docker:generate --help`.

* `-p, --php-version` - PHP version for Docker image
* `-e, --php-extensions` - PHP extensions (comma-separated) to include in Docker image
* `-o, --optimize` - Run `php artisan optimize` on container start
* `-a, --alpine` - Use Alpine Linux based images
* `-m, --node-package-manager` - Install Node dependencies using NPM or Yarn
* `-b, --node-build-tool` - Run Vite.js or Laravel Mix build step

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
