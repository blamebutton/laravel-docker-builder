<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Feature\Commands;

use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Mockery\MockInterface;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses   \BlameButton\LaravelDockerBuilder\Detectors\FileDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detectors\PhpVersionDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detectors\PhpExtensionsDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detectors\NodePackageManagerDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Detectors\NodeBuildToolDetector
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\BaseCommand::optionalChoice
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\DockerCiCommand::getArguments()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpVersionQuestion
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpExtensionsQuestion
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\ArtisanOptimizeQuestion
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\AlpineQuestion
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodePackageManagerQuestion
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodeBuildToolQuestion
 * @uses   package_path()
 *
 * @covers \BlameButton\LaravelDockerBuilder\Objects\Configuration
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
                    'COPY /package.json /package-lock.json /app/',
                    "COPY /vite.config.js /app/\n",
                    "RUN npm run build\n",
                    "RUN install-php-extensions bcmath pdo_pgsql redis\n",
                    "COPY --from=node /app/public/build/ /app/public/build/\n",
                    'RUN echo "php artisan optimize --no-ansi && php-fpm" >> /usr/bin/entrypoint.sh',
                    "CMD [\"/usr/bin/entrypoint.sh\"]\n",
                ],
                'docker:generate -n -p 8.2 -e bcmath,pdo_pgsql,redis -o -a -m npm -b vite',
            ],
            '8.1, mysql, apcu, no optimize, no alpine, yarn, mix' => [
                [
                    "FROM php:8.1-fpm AS composer\n",
                    "FROM node:lts AS node\n",
                    'COPY /package.json /yarn.lock /app/',
                    "COPY /webpack.mix.js /app/\n",
                    "RUN yarn run production\n",
                    "RUN install-php-extensions bcmath pdo_mysql apcu\n",
                    "COPY --from=node /app/public/css/ /app/public/css/\n",
                    "COPY --from=node /app/public/js/ /app/public/js/\n",
                    "COPY --from=node /app/public/fonts/ /app/public/fonts/\n",
                    'RUN echo "php-fpm" >> /usr/bin/entrypoint.sh',
                    "CMD [\"/usr/bin/entrypoint.sh\"]\n",
                ],
                'docker:generate -n -p 8.1 -e bcmath,pdo_mysql,apcu --no-optimize --no-alpine -m yarn -b mix',
            ],
        ];
    }

    /** @dataProvider provideCommands */
    public function testItGeneratesConfigurations(array $expected, string $command): void
    {
        File::deleteDirectory(base_path('.docker'));

        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->withAnyArgs()->andReturn([
                'bcmath', 'pdo_mysql', 'pdo_pgsql', 'redis', 'apcu',
            ]);
        });

        $this->artisan($command);

        $contents = file_get_contents(base_path('.docker/php.dockerfile'));

        foreach ($expected as $assertion) {
            self::assertStringContainsString($assertion, $contents);
        }
    }

    public function provideIsInformationCorrectAnswer(): array
    {
        return [
            ['yes'],
            ['no'],
        ];
    }

    /** @dataProvider provideIsInformationCorrectAnswer */
    public function testItAsksQuestions(string $isCorrect): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->with('8.2')->once()->andReturn(['bcmath', 'redis']);
            $mock->shouldReceive('get')->with(null)->andReturn(['not the same as with 8.2']);
        });

        $command = $this->artisan('docker:generate');
        $command->expectsChoice('PHP version', '8.2', ['8.2', '8.1', '8.0']);
        $command->expectsChoice('PHP extensions', ['bcmath', 'redis'], ['bcmath', 'redis']);
        $command->expectsConfirmation('Do you want to run "php artisan optimize" when the image boots?', 'yes');
        $command->expectsConfirmation('Do you want to use "Alpine Linux" based images?', 'yes');
        $command->expectsChoice('Which Node package manager do you use?', 'npm', ['npm', 'yarn', 'none']);
        $command->expectsChoice('Which Node build tool do you use?', 'vite', ['vite', 'mix']);
        $command->expectsConfirmation('Does this look correct?', $isCorrect);
        if ($isCorrect == 'yes') {
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
        } else {
            $command->expectsOutput('Exiting.');
        }
        $command->assertSuccessful();
    }

    public function provideInvalidOptions(): array
    {
        return [
            'php version' => [
                'Invalid value [unsupported] for option [php-version].',
                'docker:generate -n -p unsupported -e bcmath -o -a -m npm -b vite',
            ],
            'php extensions' => [
                'Extension [unsupported] is not supported.',
                'docker:generate -n -p 8.2 -e bcmath,unsupported -o -a -m npm -b vite',
            ],
            'node package manager' => [
                'Invalid value [unsupported] for option [node-package-manager].',
                'docker:generate -n -p 8.2 -e bcmath -o -a -m unsupported -b vite',
            ],
            'node build tool' => [
                'Invalid value [unsupported] for option [node-build-tool].',
                'docker:generate -n -p 8.2 -e bcmath -o -a -m npm -b unsupported',
            ],
        ];
    }

    /** @dataProvider provideInvalidOptions */
    public function testItThrowsExceptions(string $expected, string $command): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->withAnyArgs()->andReturn(['bcmath']);
        });

        $command = $this->artisan($command);
        $command->expectsOutput($expected);
        $command->assertFailed();
    }
}
