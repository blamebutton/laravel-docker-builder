<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\ArtisanOptimizeQuestion;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;

/**
 * @uses \BlameButton\LaravelDockerBuilder\DockerServiceProvider::boot()
 * @uses \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\ArtisanOptimize
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
            'it returns true when optimize and detect are true' => [true, true, true],
            'it returns false when optimize and detect are false' => [false, null, null],
        ];
    }

    /** @dataProvider provideOptions */
    public function testItHandlesOptionsCorrectly($expected, $optimize, $detect): void
    {
        $stub = $this->createStub(BaseCommand::class);
        $stub->method('option')
            ->willReturnMap([
                ['optimize', $optimize],
                ['detect', $detect],
            ]);

        $answer = app(ArtisanOptimizeQuestion::class)->getAnswer($stub);

        self::assertEquals($expected, $answer);
    }

    private function provideInputs(): array
    {
        return [
            'it returns true with yes' => [true, 'yes'],
            'it returns false with no' => [false, 'no'],
        ];
    }

    /** @dataProvider provideInputs */
    public function testItHandlesAnswersCorrectly($expected, $input): void
    {
        $stub = $this->createStub(BaseCommand::class);
        $stub->method('option')
            ->willReturn(false);
        $stub->method('choice')
            ->willReturn($input);

        $answer = app(ArtisanOptimizeQuestion::class)->getAnswer($stub);

        self::assertEquals($expected, $answer);
    }
}
