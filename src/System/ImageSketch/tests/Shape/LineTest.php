<?php

declare(strict_types=1);

namespace LongEssayImageSketch\Tests;

use LongEssayImageSketch\Shape\Line;
use LongEssayImageSketch\Point;
use LongEssayImageSketch\Draw;
use PHPUnit\Framework\TestCase;

class LineTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Line::class, new Line(new Point(0, 0), new Point(10, 10), 'hej', 'red'));
    }

    public function testDraw(): void
    {
        $pos = new Point(1, 1);
        $end = new Point(10, 10);
        $shifted = new Point(11, 11);
        $draw = $this->getMockBuilder(Draw::class)->getMock();
        $draw->expects(self::once())->method('withFillColor')->willReturnCallback(function ($color, callable $within) use ($draw): void {
            $this->assertSame('white', $color);
            $within($draw);
        });
        $draw->expects(self::once())->method('with')->willReturnCallback(function (array $with_what, callable $within) use ($draw): void {
            $this->assertEquals([
                'strokeColor' => 'red',
                'strokeWidth' => Line::LINE_WIDTH
            ], $with_what);

            $within($draw);
        });
        $draw->expects(self::once())->method('polygon')->with([$pos, $shifted]);
        $draw->expects(self::once())->method('shiftBy')->with($pos, $end)->willReturn($shifted);
        $draw->expects(self::once())->method('text')->with(new Point(1, -19), ' Hi ');

        $line = new Line($end, $pos, 'Hi', 'red');
        $line->draw($draw);
    }
}
