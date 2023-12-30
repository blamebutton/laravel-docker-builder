<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\AlpineQuestion;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\AlpineQuestion
 */
class AlpineQuestionTest extends TestCase
{
    public static function provideOptions(): array
    {
        return [
            'it returns true when detect is true' => [true, null, true],
            'it returns true when alpine is true' => [true, true, null],
            'it returns false when alpine is false and detect is true' => [false, false, true],
            'it returns true when alpine and detect are true' => [true, true, true],
        ];
    }

    #[DataProvider('provideOptions')]
    public function testItHandlesOptionsCorrectly($expected, $alpine, $detect): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->atMost(3))
            ->method('option')
            ->willReturnMap([
                ['alpine', $alpine],
                ['detect', $detect],
            ]);
        $mock->expects($this->never())
            ->method('confirm');

        $answer = app(AlpineQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }

    public static function provideInputs(): array
    {
        return [
            'it returns true with true' => [true, true],
            'it returns false with false' => [false, false],
        ];
    }

    #[DataProvider('provideInputs')]
    public function testItHandlesAnswersCorrectly($expected, $input): void
    {
        $stub = $this->createStub(BaseCommand::class);
        $stub->method('option')
            ->willReturn(null);
        $stub->method('confirm')
            ->willReturn($input);

        $answer = app(AlpineQuestion::class)->getAnswer($stub);

        self::assertEquals($expected, $answer);
    }
}
