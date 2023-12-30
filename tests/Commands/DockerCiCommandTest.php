<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands;

use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform;
use BlameButton\LaravelDockerBuilder\Detectors\CiPlatformDetector;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Mockery\MockInterface;
use Symfony\Component\Console\Command\Command;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\DockerGenerateCommand::getOptions()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform::name()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\CiPlatform::values()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool::values()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager::values()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions::values()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion::values()
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses   \BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\DockerCiCommand
 */
class DockerCiCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        File::delete(base_path('.gitlab-ci.yml'));
        File::deleteDirectory(base_path('.github/'));
    }

    protected function tearDown(): void
    {
        File::delete(base_path('.gitlab-ci.yml'));
        File::deleteDirectory(base_path('.github/'));

        parent::tearDown();
    }

    public function testItChecksIfGitlabFileExists(): void
    {
        touch(base_path('.gitlab-ci.yml'));

        $this->artisan('docker:ci gitlab')
            ->expectsOutput('Using [GitLab CI/CD], but [.gitlab-ci.yml] file already exists.')
            ->assertSuccessful();
    }

    public function testItChecksIfGithubFileExists(): void
    {
        File::ensureDirectoryExists(base_path('.github/workflows'));
        touch(base_path('.github/workflows/ci.yml'));

        $this->artisan('docker:ci github')
            ->expectsOutput('Using [GitHub Actions], but [.github/workflows/ci.yml] file already exists.')
            ->assertSuccessful();
    }

    public function testItUsesCiPlatformDetector(): void
    {
        $this->mock(CiPlatformDetector::class, function (MockInterface $mock) {
            $mock->expects('detect')
                ->once()
                ->withNoArgs()
                ->andReturn(CiPlatform::GITLAB_CI);
        });

        $this->artisan('docker:ci')
            ->expectsOutput(sprintf('Using [GitLab CI/CD], copying [.gitlab-ci.yml] to [%s].', base_path()))
            ->assertSuccessful();

        $this->assertFileExists(base_path('.gitlab-ci.yml'));
    }

    public function testItTellsUserToManuallyPassAPlatform(): void
    {
        $this->mock(CiPlatformDetector::class, function (MockInterface $mock) {
            $mock->expects('detect')
                ->once()
                ->withNoArgs()
                ->andReturn(false);
        });

        $this->artisan('docker:ci')
            ->expectsOutput('Unfortunately, no CI platform could be detected.')
            ->expectsOutput('Please use the [ci-platform] argument to manually define a supported platform.')
            ->assertFailed();
    }

    public function testItThrowsErrorWhenDetectorReturnsInvalid(): void
    {
        $this->mock(CiPlatformDetector::class, function (MockInterface $mock) {
            $mock->expects('detect')
                ->once()
                ->withNoArgs()
                ->andReturn('nonsense');
        });

        $this->artisan('docker:ci')
            ->expectsOutput('Invalid platform passed to BlameButton\LaravelDockerBuilder\Commands\DockerCiCommand::copy this should never happen.')
            ->assertExitCode(Command::INVALID);
    }

    public function testItErrorsOnInvalidArgument(): void
    {
        $this->artisan('docker:ci nonsense')
            ->expectsOutput('Invalid value [nonsense] for argument [ci-platform].')
            ->assertFailed();
    }
}
