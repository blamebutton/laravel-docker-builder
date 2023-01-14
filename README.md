[![Packagist Version](https://img.shields.io/packagist/v/blamebutton/laravel-docker-builder)](https://packagist.org/packages/blamebutton/laravel-docker-builder)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/blamebutton/laravel-docker-builder/phpunit.yml)

# Laravel Docker Builder

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

### Detect Configuration

```shell
php artisan docker:build --detect
```

When `--detect` is passed to the `docker:build` command, it will automatically detect the following requirements:

* PHP version, detected using the `php` version in your `composer.json`
* PHP extensions, detected using the configuration of your project:
    * Cache driver: Redis, Memcached, APC
    * Database driver: MySQL, Postgres, SQL Server
    * Broadcasting driver: Redis
    * Queue driver: Redis
    * Session driver: Redis, Memcached, APC
* Node package manager, detected using the existence of `package-lock.json` or `yarn.lock`
* Node build tool, detected using the existence of `vite.config.js` or `webpack.mix.js`
