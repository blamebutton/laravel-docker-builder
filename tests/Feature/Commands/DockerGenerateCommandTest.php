<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Feature\Commands;

use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider::boot()
 * @uses   \BlameButton\LaravelDockerBuilder\Detector\FileDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detector\PhpVersionDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detector\PhpExtensionsDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detector\NodePackageManagerDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detector\NodeBuildToolDetector
 * @uses   package_path()
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\BaseCommand::optionalChoice
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpVersionQuestion
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpExtensionsQuestion
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\ArtisanOptimizeQuestion
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\AlpineQuestion
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodePackageManagerQuestion
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodeBuildToolQuestion
 * @covers \BlameButton\LaravelDockerBuilder\Objects\Configuration
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand
 */
class DockerGenerateCommandTest extends TestCase
{
    public function provideCommands(): array
    {
        return [
            '8.2, pgsql, redis, optimize, alpine, npm, vite' => [
                [
                    "FROM php:8.2-fpm-alpine AS composer\n",
                    "FROM node:lts-alpine AS node\n",
                    "COPY /package.json /package-lock.json /app/",
                    "COPY /vite.config.js /app/\n",
                    "RUN npm run build\n",
                    "RUN install-php-extensions bcmath pdo_pgsql redis\n",
                    "COPY --from=node /app/public/build/ /app/public/build/\n",
                    "RUN echo \"php artisan optimize --no-ansi && php-fpm\" >> /usr/bin/entrypoint.sh",
                    "CMD [\"/usr/bin/entrypoint.sh\"]\n"
                ],
                'docker:generate -n -p 8.2 -e bcmath,pdo_pgsql,redis -o -a -m npm -b vite',
            ],
            '8.1, mysql, apcu, no optimize, no alpine, yarn, mix' => [
                [
                    "FROM php:8.1-fpm AS composer\n",
                    "FROM node:lts AS node\n",
                    "COPY /package.json /yarn.lock /app/",
                    "COPY /webpack.mix.js /app/\n",
                    "RUN yarn run production\n",
                    "RUN install-php-extensions bcmath pdo_mysql apcu\n",
                    "COPY --from=node /app/public/css/ /app/public/css/\n",
                    "COPY --from=node /app/public/js/ /app/public/js/\n",
                    "COPY --from=node /app/public/fonts/ /app/public/fonts/\n",
                    "RUN echo \"php-fpm\" >> /usr/bin/entrypoint.sh",
                    "CMD [\"/usr/bin/entrypoint.sh\"]\n"
                ],
                'docker:generate -n -p 8.1 -e bcmath,pdo_mysql,apcu --no-optimize --no-alpine -m yarn -b mix',
            ],
        ];
    }

    /** @dataProvider provideCommands */
    public function testItGeneratesConfigurations(array $expected, string $command): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetch')->withAnyArgs()->andReturn([
                'bcmath', 'pdo_mysql', 'pdo_pgsql', 'redis', 'apcu',
            ]);
        });

        $this->artisan($command);

        $contents = file_get_contents(base_path('.docker/php.dockerfile'));

        foreach ($expected as $assertion) {
            self::assertStringContainsString($assertion, $contents);
        }
    }

    public function testItAsksQuestions(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('fetch')->with('8.2')->once()->andReturn(['bcmath', 'redis']);
            $mock->shouldReceive('fetch')->with(null)->andReturn(['not the same as with 8.2']);
        });

        $command = $this->artisan('docker:generate');
        $command->expectsChoice('PHP version', '8.2', ['8.2', '8.1', '8.0']);
        $command->expectsChoice('PHP extensions', ['bcmath', 'redis'], ['bcmath', 'redis']);
        $command->expectsConfirmation('Do you want to run "php artisan optimize" when the image boots?', 'yes');
        $command->expectsConfirmation('Do you want to use "Alpine Linux" based images?', 'yes');
        $command->expectsChoice('Which Node package manager do you use?', 'npm', ['npm', 'yarn', 'none']);
        $command->expectsChoice('Which Node build tool do you use?', 'vite', ['vite', 'mix']);
        $command->expectsConfirmation('Does this look correct?', 'yes');
        $command->expectsOutput('Configuration:');
        $command->expectsTable(['Key', 'Value'], [
            ['PHP version', '8.2'],
            ['PHP extensions', 'bcmath, redis'],
            ['Artisan Optimize', 'true'],
            ['Alpine images', 'true'],
            ['Node package manager', 'NPM'],
            ['Node build tool', 'Vite.js'],
        ]);
        $command->expectsOutput('Command to generate above configuration:');
        $command->expectsOutput('  php artisan docker:generate -n -p 8.2 -e bcmath,redis -o -a -m npm -b vite');
    }
}
