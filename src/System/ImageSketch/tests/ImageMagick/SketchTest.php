<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\Tests\ImageMagick;

use PHPUnit\Framework\TestCase;
use Edutiek\AssessmentService\System\ImageSketch\ImageMagick\Sketch;
use Edutiek\AssessmentService\System\ImageSketch\Shape;
use Edutiek\AssessmentService\System\ImageSketch\Draw;
use Imagick;

class SketchTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(Sketch::class, new Sketch());
    }

    public function testApplyShapes(): void
    {
        $magic = new Imagick();
        $magic->readImage($this->dummyPNG());
        $format = $magic->identifyFormat('%m');
        $width = $magic->getImageWidth();
        $height = $magic->getImageHeight();
        $magic->destroy();

        $shape = $this->getMockBuilder(Shape::class)->getMock();
        $shape->expects(self::exactly(2))->method('draw')->willReturnCallback(function ($draw) {
            $this->assertInstanceOf(Draw::class, $draw);
        });

        $paint = new Sketch();
        $image = fopen($this->dummyPNG(), 'r');
        $fd = $paint->applyShapes([$shape, $shape], $image);
        fclose($image);
        $magic = new Imagick();
        $magic->readImageFile($fd);
        $this->assertSame($format, $magic->identifyFormat('%m'));
        $this->assertSame($width, $magic->getImageWidth());
        $this->assertSame($height, $magic->getImageHeight());
        fclose($fd);
        $magic->destroy();
    }

    private function dummyPNG(): string
    {
        return __DIR__ . '/Test.png';
    }
}
