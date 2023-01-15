<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\ArtisanOptimizeQuestion;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\ArtisanOptimizeQuestion
 */
class ArtisanOptimizeQuestionTest extends TestCase
{
    private function provideOptions(): array
    {
        return [
            'it returns true when detect is true' => [true, null, true],
            'it returns true when optimize is true' => [true, true, null],
            'it returns false when optimize is false and detect is true' => [false, false, true],
            'it returns true when optimize and detect are true' => [true, true, true],
        ];
    }

    /** @dataProvider provideOptions */
    public function testItHandlesOptionsCorrectly($expected, $optimize, $detect): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->atMost(3))
            ->method('option')
            ->willReturnMap([
                ['optimize', $optimize],
                ['detect', $detect],
            ]);
        $mock->expects($this->never())
            ->method('confirm');

        $answer = app(ArtisanOptimizeQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }

    private function provideInputs(): array
    {
        return [
            'it returns true with true' => [true, true],
            'it returns false with false' => [false, false],
        ];
    }

    /** @dataProvider provideInputs */
    public function testItHandlesAnswersCorrectly($expected, $input): void
    {
        $stub = $this->createStub(BaseCommand::class);
        $stub->method('option')
            ->willReturn(null);
        $stub->method('confirm')
            ->willReturn($input);

        $answer = app(ArtisanOptimizeQuestion::class)->getAnswer($stub);

        self::assertEquals($expected, $answer);
    }
}
