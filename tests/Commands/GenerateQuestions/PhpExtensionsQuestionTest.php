<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpExtensionsQuestion;
use BlameButton\LaravelDockerBuilder\Detectors\PhpExtensionsDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Integrations\SupportedPhpExtensions;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpExtensions
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpExtensionsQuestion
 */
class PhpExtensionsQuestionTest extends TestCase
{
    public function testItValidatesOptionInput(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->once()
                ->with(PhpVersion::v8_3)
                ->andReturn(['bcmath', 'pdo_mysql']);
        });

        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->with('php-extensions')
            ->willReturn('bcmath,pdo_mysql,redis');

        $this->expectException(InvalidOptionValueException::class);
        $this->expectExceptionMessage('Extension [redis] is not supported.');

        app(PhpExtensionsQuestion::class)->getAnswer($mock, PhpVersion::v8_3);
    }

    public function testItUsesOptionInput(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (SupportedPhpExtensions&MockInterface $mock) {
            $mock->shouldReceive('get')
                ->once()
                ->with(PhpVersion::v8_3)
                ->andReturn(['bcmath', 'pdo_mysql', 'redis']);
        });

        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->with('php-extensions')
            ->willReturn('bcmath,pdo_mysql');

        $answer = app(PhpExtensionsQuestion::class)->getAnswer($mock, PhpVersion::v8_3);

        self::assertEquals(['bcmath', 'pdo_mysql'], $answer);
    }

    public function testItDetectsExtensions(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->once()
                ->andReturn(['bcmath', 'pdo_mysql', 'redis']);
        });

        $this->partialMock(PhpExtensionsDetector::class, function (PhpExtensionsDetector&MockInterface $mock) {
            $mock->shouldReceive('supported')
                ->once()
                ->with(['bcmath', 'pdo_mysql', 'redis'])
                ->andReturnSelf();
            $mock->shouldReceive('detect')
                ->once()
                ->withNoArgs()
                ->andReturn(['bcmath', 'pdo_mysql']);
        });

        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(2))
            ->method('option')
            ->willReturnMap([
                ['php-extensions', null],
                ['detect', true],
            ]);

        $answer = app(PhpExtensionsQuestion::class)->getAnswer($mock, PhpVersion::v8_3);

        self::assertEquals(['bcmath', 'pdo_mysql'], $answer);
    }

    public function testItAsksForInput(): void
    {
        $this->mock(SupportedPhpExtensions::class, function (MockInterface $mock) {
            $mock->shouldReceive('get')
                ->once()
                ->andReturn(['bcmath', 'pdo_mysql', 'redis']);
        });

        $this->partialMock(PhpExtensionsDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('supported')
                ->once()
                ->andReturnSelf();
            $mock->shouldReceive('detect')
                ->once()
                ->andReturn([]);
        });

        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(2))
            ->method('option')
            ->willReturnMap([
                ['php-extensions', null],
                ['detect', null],
            ]);
        $mock->expects($this->once())
            ->method('choice')
            ->willReturn(['bcmath', 'pdo_mysql']);

        $answer = app(PhpExtensionsQuestion::class)->getAnswer($mock, PhpVersion::v8_3);

        self::assertEquals(['bcmath', 'pdo_mysql'], $answer);
    }
}
