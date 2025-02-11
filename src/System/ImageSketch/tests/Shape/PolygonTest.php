<?php

declare(strict_types=1);

namespace LongEssayImageSketch\Tests;

use PHPUnit\Framework\TestCase;
use LongEssayImageSketch\Shape\Polygon;
use LongEssayImageSketch\Point;
use LongEssayImageSketch\Draw;

class PolygonTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Polygon::class, new Polygon([], new Point(0, 0), 'Hej', 'red'));
    }

    public function testDraw(): void
    {
        $colors = ['green', 'white'];
        $pos = new Point(20, 23);
        $polygon = [new Point(1, 1), new Point(40, 40), new Point(89, 0)];
        $shifted = [new Point(21, 24), new Point(60, 63), new Point(109, 23)];

        $draw = $this->getMockBuilder(Draw::class)->getMock();
        $draw->expects(self::exactly(count($colors)))->method('withFillColor')->willReturnCallback(function ($color, $within) use ($draw, &$colors) {
            $this->assertSame(array_shift($colors), $color);
            $within($draw);
        });
        $draw->expects(self::once())->method('shiftAllBy')->with($pos, $polygon)->willReturn($shifted);
        $draw->expects(self::once())->method('polygon')->with($shifted);
        $draw->expects(self::once())->method('text')->with(new Point(20, 3), ' Hej ');

        $polygon = new Polygon($polygon, $pos, 'Hej', 'green');
        $polygon->draw($draw);
    }
}
