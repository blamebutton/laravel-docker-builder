<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\NodeBuildTool;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\NodeBuildToolQuestion;
use BlameButton\LaravelDockerBuilder\Detector\NodeBuildToolDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

class NodeBuildToolQuestionTest extends TestCase
{
    public function testItThrowsErrorOnInvalidInput(): void
    {
        $stub = $this->createMock(BaseCommand::class);
        $stub->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['node-build-tool', 'invalid-value'],
            ]);

        $this->expectException(InvalidOptionValueException::class);

        app(NodeBuildToolQuestion::class)->getAnswer($stub);
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
}
