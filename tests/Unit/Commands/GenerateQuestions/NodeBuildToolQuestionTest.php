<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodeBuildToolQuestion;
use BlameButton\LaravelDockerBuilder\Detector\NodeBuildToolDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider::boot()
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodeBuildToolQuestion
 */
class NodeBuildToolQuestionTest extends TestCase
{
    public function testItThrowsErrorOnInvalidInput(): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['node-build-tool', 'invalid-value'],
            ]);

        $this->expectException(InvalidOptionValueException::class);

        app(NodeBuildToolQuestion::class)->getAnswer($mock);
    }

    private function provideOptions(): array
    {
        return [
            'vite' => [NodeBuildTool::VITE, 'vite'],
            'mix' => [NodeBuildTool::MIX, 'mix'],
        ];
    }

    /** @dataProvider provideOptions */
    public function testItHandlesOptions($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['node-build-tool', $input],
            ]);

        $answer = app(NodeBuildToolQuestion::class)->getAnswer($mock);
    }

    public function provideDetectedBuildTools(): array
    {
        return [
            'vite' => [NodeBuildTool::VITE, NodeBuildTool::VITE],
            'mix' => [NodeBuildTool::MIX, NodeBuildTool::MIX],
        ];
    }

    /** @dataProvider provideDetectedBuildTools */
    public function testItDetectsBuildTools($expected, $detected): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(2))
            ->method('option')
            ->willReturnMap([
                ['node-build-tool', null],
                ['detect', true],
            ]);

        $this->mock(NodeBuildToolDetector::class, function (MockInterface $mock) use ($detected) {
            $mock->shouldReceive('detect')->once()->andReturn($detected);
        });

        $answer = app(NodeBuildToolQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }

    public function provideQuestionInput(): array
    {
        return [
            'vite' => ['vite', 'vite'],
            'mix' => ['mix', 'mix'],
        ];
    }

    /** @dataProvider provideQuestionInput */
    public function testItAsksQuestion($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(2))
            ->method('option')
            ->willReturnMap([
                ['node-build-tool', null],
                ['detect', false],
            ]);
        $mock->expects($this->once())
            ->method('choice')
            ->willReturn($input);

        $this->mock(NodeBuildToolDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('detect')->once()->andReturn(false);
        });

        $answer = app(NodeBuildToolQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }
}
