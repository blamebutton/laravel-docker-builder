<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\AlpineQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\ArtisanOptimizeQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpExtensionsQuestion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpVersionQuestion;
use BlameButton\LaravelDockerBuilder\Detectors\NodePackageManagerDetector;
use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')->withAnyArgs()->andReturn([
                'bcmath', 'pdo_mysql', 'pdo_pgsql', 'redis', 'apcu',
            ]);
        });
    }

    public static function provideCommands(): array
    {
        return [
            '8.2, pgsql, redis, optimize, alpine, npm, vite' => [
                [
                    "FROM php:8.2-fpm-alpine AS composer\n",
                    "FROM node:lts-alpine AS node\n",
                    'COPY /package.json /package-lock.json /app/',
                    'COPY /package.json /package-lock.json /app/',
                    "npm ci\n",
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
                    "RUN yarn install\n",
                    "COPY /*.js /*.ts /app/\n",
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

    #[DataProvider('provideCommands')]
    public function testItGeneratesConfigurations(array $expected, string $command): void
    {
        File::deleteDirectory(base_path('.docker'));

        $this->artisan($command);

        $contents = file_get_contents(base_path('.docker/php.dockerfile'));

        foreach ($expected as $assertion) {
            self::assertStringContainsString($assertion, $contents);
        }
    }

    public static function provideIsInformationCorrectAnswer(): array
    {
        return [
            ['yes'],
            ['no'],
        ];
    }

    #[DataProvider('provideIsInformationCorrectAnswer')]
    public function testItAsksQuestions(string $isCorrect): void
    {
        $this->mock(SupportedPhpExtensions::class, function (SupportedPhpExtensions&MockInterface $mock) {
            $mock->shouldReceive('get')->with(PhpVersion::v8_3)->once()->andReturn(['bcmath', 'redis']);
            $mock->shouldReceive('get')->with(null)->andReturn(['not the same as with 8.3']);
        });

        $command = $this->artisan('docker:generate');
        $command->expectsChoice('PHP version', '8.3', ['8.3', '8.2', '8.1']);
        $command->expectsChoice('PHP extensions', ['bcmath', 'redis'], ['bcmath', 'redis']);
        $command->expectsConfirmation('Do you want to run "php artisan optimize" when the image boots?', 'yes');
        $command->expectsConfirmation('Do you want to use "Alpine Linux" based images?', 'yes');
        $command->expectsChoice('Which Node package manager do you use?', 'npm', ['npm', 'yarn', 'none']);
        $command->expectsChoice('Which Node build tool do you use?', 'vite', ['vite', 'mix']);
        $command->expectsConfirmation('Does this look correct?', $isCorrect);
        if ($isCorrect === 'yes') {
            $command->expectsOutput('Configuration:');
            $command->expectsTable(['Key', 'Value'], [
                ['PHP version', '8.3'],
                ['PHP extensions', 'bcmath, redis'],
                ['Artisan Optimize', 'true'],
                ['Alpine images', 'true'],
                ['Node package manager', 'NPM'],
                ['Node build tool', 'Vite.js'],
            ]);
            $command->expectsOutput('Command to generate above configuration:');
            $command->expectsOutput('  php artisan docker:generate -n -p 8.3 -e bcmath,redis -o -a -m npm -b vite');
        } else {
            $command->expectsOutput('Exiting.');
        }
        $command->assertSuccessful();
    }

    public static function provideInvalidOptions(): array
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

    #[DataProvider('provideInvalidOptions')]
    public function testItThrowsExceptions(string $expected, string $command): void
    {
        $command = $this->artisan($command);
        $command->expectsOutput($expected);
        $command->assertFailed();
    }

    public function testItAcceptsNodePackageManagerNone(): void
    {
        $this->mock(PhpVersionQuestion::class, function (PhpVersionQuestion&MockInterface $mock) {
            $mock->shouldReceive('getAnswer')->once()->andReturn('8.2');
        });
        $this->mock(PhpExtensionsQuestion::class, function (PhpExtensionsQuestion&MockInterface $mock) {
            $mock->shouldReceive('getAnswer')->once()->andReturn(['bcmath']);
        });
        $this->mock(ArtisanOptimizeQuestion::class, function (ArtisanOptimizeQuestion&MockInterface $mock) {
            $mock->shouldReceive('getAnswer')->once()->andReturn(true);
        });
        $this->mock(AlpineQuestion::class, function (AlpineQuestion&MockInterface $mock) {
            $mock->shouldReceive('getAnswer')->once()->andReturn(true);
        });
        $this->mock(NodePackageManagerDetector::class, function (NodePackageManagerDetector&MockInterface $mock) {
            $mock->shouldReceive('detect')->once()->andReturn(false);
        });

        $this->artisan('docker:generate', ['--detect' => true])
            ->expectsChoice('Which Node package manager do you use?', 'none', ['npm', 'yarn', 'none'])
            ->expectsConfirmation('Does this look correct?', 'yes');
    }
}
