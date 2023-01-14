<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodePackageManager;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodePackageManagerQuestion;
use BlameButton\LaravelDockerBuilder\Detector\NodePackageManagerDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider::boot()
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

    private function provideOptions(): array
    {
        return [
            'npm' => [NodePackageManager::NPM, 'npm'],
            'yarn' => [NodePackageManager::YARN, 'yarn'],
        ];
    }

    /** @dataProvider provideOptions */
    public function testItHandlesOptions($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['node-package-manager', $input],
            ]);

        $answer = app(NodePackageManagerQuestion::class)->getAnswer($mock);
    }

    public function provideDetectedPackageManagers(): array
    {
        return [
            'npm' => [NodePackageManager::NPM, NodePackageManager::NPM],
            'yarn' => [NodePackageManager::YARN, NodePackageManager::YARN],
        ];
    }

    /** @dataProvider provideDetectedPackageManagers */
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

    public function provideQuestionInput(): array
    {
        return [
            'npm' => ['npm', 'npm'],
            'yarn' => ['yarn', 'yarn'],
        ];
    }

    /** @dataProvider provideQuestionInput */
    public function testItAsksQuestion($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(2))
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
