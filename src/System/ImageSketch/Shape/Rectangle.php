<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\Shape;

use Edutiek\AssessmentService\System\ImageSketch\Draw;
use Edutiek\AssessmentService\System\ImageSketch\Point;

class Rectangle extends NoShape
{
    private float $width;
    private float $height;

    public function __construct(float $width, float $height, ...$args)
    {
        $this->width = $width;
        $this->height = $height;
        parent::__construct(...$args);
    }

    public function draw(Draw $draw): void
    {
        $draw->withFillColor($this->color(), function($draw) {
            $draw->polygon($draw->shiftAllBy($this->pos(), [
                new Point(0, 0),
                new Point($this->width, 0),
                new Point($this->width, $this->height),
                new Point(0, $this->height),
            ]));
        });
        $this->drawLabel($draw);
    }
}
