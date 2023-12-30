<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodePackageManagerQuestion;
use BlameButton\LaravelDockerBuilder\Detectors\NodePackageManagerDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodePackageManagerQuestion
 */
class NodePackageManagerQuestionTest extends TestCase
{
    public function testItThrowsErrorOnInvalidInput(): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['node-package-manager', 'invalid-value'],
            ]);

        $this->expectException(InvalidOptionValueException::class);

        app(NodePackageManagerQuestion::class)->getAnswer($mock);
    }

    public static function provideOptions(): array
    {
        return [
            'npm' => [NodePackageManager::NPM, 'npm'],
            'yarn' => [NodePackageManager::YARN, 'yarn'],
        ];
    }

    #[DataProvider('provideOptions')]
    public function testItHandlesOptions($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['node-package-manager', $input],
            ]);

        $answer = app(NodePackageManagerQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }

    public static function provideDetectedPackageManagers(): array
    {
        return [
            'npm' => [NodePackageManager::NPM, NodePackageManager::NPM],
            'yarn' => [NodePackageManager::YARN, NodePackageManager::YARN],
        ];
    }

    #[DataProvider('provideDetectedPackageManagers')]
    public function testItDetectsPackageManagers($expected, $detected): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(2))
            ->method('option')
            ->willReturnMap([
                ['node-package-manager', null],
                ['detect', true],
            ]);

        $this->mock(NodePackageManagerDetector::class, function (MockInterface $mock) use ($detected) {
            $mock->shouldReceive('detect')->once()->andReturn($detected);
        });

        $answer = app(NodePackageManagerQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }

    public static function provideQuestionInput(): array
    {
        return [
            'npm' => ['npm', 'npm'],
            'yarn' => ['yarn', 'yarn'],
        ];
    }

    #[DataProvider('provideQuestionInput')]
    public function testItAsksQuestion($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['node-package-manager', null],
                ['detect', false],
            ]);
        $mock->expects($this->once())
            ->method('optionalChoice')
            ->willReturn($input);

        $this->mock(NodePackageManagerDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('detect')->once()->andReturn(false);
        });

        $answer = app(NodePackageManagerQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }
}
