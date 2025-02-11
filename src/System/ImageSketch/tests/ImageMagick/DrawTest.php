<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\Tests\ImageMagick;

use PHPUnit\Framework\TestCase;
use Edutiek\AssessmentService\System\ImageSketch\ImageMagick\Draw;
use Edutiek\AssessmentService\System\ImageSketch\Shape;
use Edutiek\AssessmentService\System\ImageSketch\Point;
use ImagickDraw;
use ImagickPixel;
use Imagick;
use Closure;

class DrawTest extends TestCase
{
    public function testConstruct(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(Draw::class, new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock()));
    }

    public function testPath(): void
    {
        $called_times = 0;
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('pathStart');
        $magic->expects(self::once())->method('pathFinish');
        $magic->expects(self::once())->method('pathMoveToAbsolute')->with(10, 11);

        $segment = function ($draw) use ($magic, &$called_times) {
            $called_times++;
            $this->assertSame($magic, $draw);
        };

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->path(new Point(10, 11), [$segment, $segment, $segment]);
        $this->assertSame(3, $called_times);
    }

    public function testLineTo(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('pathLineToAbsolute')->with(3, 4);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw_line = $draw->lineTo(new Point(3, 4));
        $this->assertInstanceOf(Closure::class, $draw_line);

        $draw_line($magic, new Point(0, 0));
    }

    public function testQuadraticCurve(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('pathCurveToQuadraticBezierAbsolute')->with(8, 10, 5, 6);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw_curve = $draw->quadraticCurve(new Point(3, 4), new Point(5, 6));
        $this->assertInstanceOf(Closure::class, $draw_curve);
        $draw_curve($magic, new Point(0, 0));
    }

    public function testWithRotation(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::exactly(2))->method('rotate')->withConsecutive([45], [-45]);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->withRotation(1/4 * pi(), function ($d) use ($draw) {
            $this->assertSame($draw, $d);
        });
    }

    public function testPolygonWithEmptyPoints(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::never())->method('pathStart');

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->polygon([]);
    }

    public function testPolygonWithPoints(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('pathStart');
        $magic->expects(self::once())->method('pathFinish');
        $magic->expects(self::once())->method('pathMoveToAbsolute')->with(23, 32);
        $magic->expects(self::once())->method('pathLineToAbsolute')->with(9, 8);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->polygon([new Point(23, 32), new Point(9, 8)]);
    }

    public function testCircle(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('circle')->with(33, 44, 45, 56);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->circle(new Point(34, 45), 12);
    }

    public function testWithFillColor(): void
    {
        $green = $this->getMockBuilder(ImagickPixel::class)->disableOriginalConstructor()->getMock();
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('getFillColor')->willReturn($green);
        $magic->expects(self::exactly(2))->method('setFillColor')->withConsecutive(['red'], [$green]);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->withFillColor('red', function ($d) use ($draw) {
            $this->assertSame($draw, $d);
        });
    }

    public function testWithStrokeColor(): void
    {
        $green = $this->getMockBuilder(ImagickPixel::class)->disableOriginalConstructor()->getMock();
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('getStrokeColor')->willReturn($green);
        $magic->expects(self::exactly(2))->method('setStrokeColor')->withConsecutive(['red'], [$green]);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->withStrokeColor('red', function ($d) use ($draw) {
            $this->assertSame($draw, $d);
        });
    }

    public function testWithStrokeWidth(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('getStrokeWidth')->willReturn(8.0);
        $magic->expects(self::exactly(2))->method('setStrokeWidth')->withConsecutive([3.0], [8.0]);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->withStrokeWidth(3.0, function ($d) use ($draw) {
            $this->assertSame($draw, $d);
        });
    }


    public function testWithOriginAt(): void
    {
        $origin = new Point(40, 76);

        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::exactly(2))->method('pathMoveToAbsolute')->withConsecutive(
            [$origin->x(), $origin->y()],
            [0, 0]
        );
        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());

        $draw->withOriginAt($origin, function ($d) use ($draw): void {
            $this->assertSame($draw, $d);
            $draw->path(new Point(0, 0), []);
        });
        $draw->path(new Point(0, 0), []);
    }

    public function testShiftBy(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $shifted = $draw->shiftBy(new Point(1, 2), new Point(10, 20));
        $this->assertEquals(new Point(11, 22), $shifted);
    }

    public function testShiftAllBy(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $shifted = $draw->shiftAllBy(new Point(1, 2), [new Point(10, 20), new Point(40, 90)]);
        $this->assertEquals([new Point(11, 22), new Point(41, 92)], $shifted);
    }

    public function testText(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('annotation')->with(3, 4, 'Hej');

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->text(new Point(3, 4), 'Hej');
    }

    public function testWithCenteredText(): void
    {
        $parent = $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock();

        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('annotation')->with(20, 33, 'Hej');
        $magic->expects(self::once())->method('getTextAlignment')->willReturn(456);
        $magic->expects(self::exactly(2))->method('setTextAlignment')->withConsecutive([Imagick::ALIGN_CENTER], [456]);
        $parent->expects(self::once())->method('queryFontMetrics')->with($magic, 'Hej', false)->willReturn(['ascender' => 6]);

        $draw = new Draw($magic, $parent);
        $draw->withCenteredText(fn ($draw) => $draw->text(new Point(20, 30), 'Hej'));
    }

    public function testWithFontSize(): void
    {
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::once())->method('annotation')->with(20, 30, 'Hej');
        $magic->expects(self::once())->method('getFontSize')->willReturn(456.0);
        $magic->expects(self::exactly(2))->method('setFontSize')->withConsecutive([23], [456]);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());
        $draw->withFontSize(23, fn ($draw) => $draw->text(new Point(20, 30), 'Hej'));
    }

    public function testWith(): void
    {
        $pixel = $this->getMockBuilder(ImagickPixel::class)->disableOriginalConstructor()->getMock();
        $magic = $this->getMockBuilder(ImagickDraw::class)->disableOriginalConstructor()->getMock();
        $magic->expects(self::exactly(2))->method('setFillColor')->withConsecutive(['red'], [$pixel]);
        $magic->expects(self::once())->method('getFillColor')->willReturn($pixel);
        $magic->expects(self::exactly(2))->method('setStrokeColor')->withConsecutive(['green'], [$pixel]);
        $magic->expects(self::once())->method('getStrokeColor')->willReturn($pixel);

        $draw = new Draw($magic, $this->getMockBuilder(Imagick::class)->disableOriginalConstructor()->getMock());

        $draw->with([
            'fillColor' => 'red',
            'strokeColor' => 'green',
        ], function ($d) use ($draw) {
            $this->assertSame($draw, $d);
        });
    }
}
