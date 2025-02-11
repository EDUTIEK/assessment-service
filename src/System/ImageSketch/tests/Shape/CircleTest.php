<?php

declare(strict_types=1);

namespace LongEssayImageSketch\Tests;

use PHPUnit\Framework\TestCase;
use LongEssayImageSketch\Shape\Circle;
use LongEssayImageSketch\Point;
use LongEssayImageSketch\Draw;

class CircleTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Circle::class, new Circle('X', 'red', 22, new Point(0, 0), 'Hej', 'red'));
    }

    public function testDraw(): void
    {
        $colors = ['red', 'green', 'white'];
        $pos = new Point(10, 10);
        $draw = $this->getMockBuilder(Draw::class)->getMock();
        $draw->expects(self::exactly(count($colors)))->method('withFillColor')->willReturnCallback(function ($color, $within) use ($draw, &$colors) {
            $this->assertSame(array_shift($colors), $color);
            $within($draw);
        });
        $draw->expects(self::once())->method('circle')->with($pos, 70);
        $draw->expects(self::once())->method('withCenteredText')->willReturnCallback(function (callable $within) use ($draw): void {
            $within($draw);
        });
        $draw->expects(self::once())->method('withFontSize')->willReturnCallback(function (int $size, callable $within) use ($draw): void {
            $this->assertSame(22, $size);
            $within($draw);
        });
        $draw->expects(self::exactly(2))->method('text')->withConsecutive(
            [new Point(10, 0), 'X'],
            [new Point(10, -110), ' Hej ']
        );

        $circle = new Circle('X', 'green', 22, $pos, 'Hej', 'red');
        $circle->draw($draw);
    }
}
