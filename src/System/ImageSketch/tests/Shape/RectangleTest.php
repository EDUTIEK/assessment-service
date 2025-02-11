<?php

declare(strict_types=1);

namespace LongEssayImageSketch\Tests;

use PHPUnit\Framework\TestCase;
use LongEssayImageSketch\Shape\Rectangle;
use LongEssayImageSketch\Point;
use LongEssayImageSketch\Draw;

class RectangleTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Rectangle::class, new Rectangle(10, 10, new Point(0, 0), 'Hej', 'red'));
    }

    public function testDraw(): void
    {
        $colors = ['blue', 'white'];
        $pos = new Point(37, 87);
        $width = 22;
        $height = 51;
        $shifted = [new Point(10101, 10022), new Point(20202, 39)];
        $rect_as_polygon = [
            new Point(0, 0),
            new Point($width, 0),
            new Point($width, $height),
            new Point(0, $height),
        ];

        $draw = $this->getMockBuilder(Draw::class)->getMock();
        $draw->expects(self::exactly(count($colors)))->method('withFillColor')->willReturnCallback(function ($color, $within) use ($draw, &$colors) {
            $this->assertSame(array_shift($colors), $color);
            $within($draw);
        });
        $draw->expects(self::once())->method('shiftAllBy')->with($pos, $rect_as_polygon)->willReturn($shifted);
        $draw->expects(self::once())->method('polygon')->with($shifted);
        $draw->expects(self::once())->method('text')->with(new Point(37, 67), ' Hej ');

        $rectangle = new Rectangle($width, $height, $pos, 'Hej', 'blue');
        $rectangle->draw($draw);
    }
}
