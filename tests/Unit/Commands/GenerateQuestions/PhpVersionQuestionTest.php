<?php

namespace BlameButton\LaravelDockerBuilder\Tests\Unit\Commands\GenerateQuestions;

use BlameButton\LaravelDockerBuilder\Commands\BaseCommand;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion;
use BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpVersionQuestion;
use BlameButton\LaravelDockerBuilder\Detectors\PhpVersionDetector;
use BlameButton\LaravelDockerBuilder\Exceptions\InvalidOptionValueException;
use BlameButton\LaravelDockerBuilder\Tests\TestCase;
use Mockery\MockInterface;

/**
 * @uses   \BlameButton\LaravelDockerBuilder\DockerServiceProvider
 * @uses   \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\Choices\PhpVersion
 *
 * @covers \BlameButton\LaravelDockerBuilder\Commands\GenerateQuestions\PhpVersionQuestion
 */
class PhpVersionQuestionTest extends TestCase
{
    public function testItThrowsErrorOnInvalidInput(): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['php-version', 'invalid-value'],
            ]);

        $this->expectException(InvalidOptionValueException::class);

        app(PhpVersionQuestion::class)->getAnswer($mock);
    }

    private function provideOptions(): array
    {
        return [
            '8.2' => [PhpVersion::v8_2, '8.2'],
            '8.1' => [PhpVersion::v8_1, '8.1'],
            '8.0' => [PhpVersion::v8_0, '8.0'],
        ];
    }

    /** @dataProvider provideOptions */
    public function testItHandlesOptions($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->once())
            ->method('option')
            ->willReturnMap([
                ['php-version', $input],
            ]);

        $answer = app(PhpVersionQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }

    public function provideDetected(): array
    {
        return [
            '8.2' => [PhpVersion::v8_2, '8.2'],
            '8.1' => [PhpVersion::v8_1, '8.1'],
            '8.0' => [PhpVersion::v8_0, '8.0'],
        ];
    }

    /** @dataProvider provideDetected */
    public function testItDetectsPackageManagers($expected, $detected): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(2))
            ->method('option')
            ->willReturnMap([
                ['php-version', null],
                ['detect', true],
            ]);

        $this->mock(PhpVersionDetector::class, function (MockInterface $mock) use ($detected) {
            $mock->shouldReceive('detect')->once()->andReturn($detected);
        });

        $answer = app(PhpVersionQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }

    public function provideQuestionInput(): array
    {
        return [
            '8.2' => ['8.2', '8.2'],
            '8.1' => ['8.1', '8.1'],
            '8.0' => ['8.0', '8.0'],
        ];
    }

    /** @dataProvider provideQuestionInput */
    public function testItAsksQuestion($expected, $input): void
    {
        $mock = $this->createMock(BaseCommand::class);
        $mock->expects($this->exactly(1))
            ->method('option')
            ->willReturnMap([
                ['php-version', null],
                ['detect', false],
            ]);
        $mock->expects($this->once())
            ->method('choice')
            ->willReturn($input);

        $this->mock(PhpVersionDetector::class, function (MockInterface $mock) {
            $mock->shouldReceive('detect')->once()->andReturn(false);
        });

        $answer = app(PhpVersionQuestion::class)->getAnswer($mock);

        self::assertEquals($expected, $answer);
    }
}
