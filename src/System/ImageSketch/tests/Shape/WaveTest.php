<?php

declare(strict_types=1);

namespace LongEssayImageSketch\Tests;

use PHPUnit\Framework\TestCase;
use LongEssayImageSketch\Shape\Wave;
use LongEssayImageSketch\Point;
use LongEssayImageSketch\Draw;

class WaveTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Wave::class, new Wave(new Point(0, 10), new Point(0, 0), 'Hej', 'red'));
    }

    public function testDraw(): void
    {
        $start = new Point(23, 34);
        $end = new Point(73, 33);
        $expected_path = [function(){}, function(){}];
        $shifted = [new Point(1, 1), new Point(2, 2), new Point(3, 3), new Point(4, 4), new Point(5, 5)];

        $draw = $this->getMockBuilder(Draw::class)->getMock();
        $draw->expects(self::once())->method('with')->willReturnCallback(function (array $with_what, callable $within) use ($draw) {
            $this->assertEquals([
                'rotation',
                'strokeColor',
                'strokeWidth',
                'originAt',
            ], array_keys($with_what));
            $within($draw);
        });
        $draw->expects(self::once())->method('path')->willReturnCallback(function ($start, $path) use ($draw, $expected_path) {
            $this->assertEquals(
                new Point(0, 0),
                $start
            );

            $this->assertEquals($expected_path, $path);

        });
        $draw->expects(self::exactly(count($expected_path)))->method('quadraticCurve')->willReturnOnConsecutiveCalls(...$expected_path);
        $draw->expects(self::any())->method('shiftBy')->willReturnOnConsecutiveCalls(...$shifted);
        $draw->expects(self::once())->method('text')->with(new Point(23, 14), ' Hej ');
        $draw->expects(self::once())->method('withFillColor')->willReturnCallback(function ($color, $within) use ($draw) {
            $this->assertSame('white', $color);
            $within($draw);
        });

        $wave = new Wave($end, $start, 'Hej', 'yellow');
        $wave->draw($draw);
    }
}
