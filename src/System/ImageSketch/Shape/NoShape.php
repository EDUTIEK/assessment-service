<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\Shape;

use Edutiek\AssessmentService\System\ImageSketch\Shape;
use Edutiek\AssessmentService\System\ImageSketch\Point;
use Edutiek\AssessmentService\System\ImageSketch\Draw;

abstract class NoShape implements Shape
{
    private Point $pos;
    private string $label;
    private string $color;

    public function __construct(Point $pos, string $label, string $color)
    {
        $this->pos = $pos;
        $this->label = $label;
        $this->color = $color;
    }

    public function pos(): Point
    {
        return $this->pos;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function color(): string
    {
        return $this->color;
    }

    protected function drawLabel(Draw $draw, ?Point $pos = null): void
    {
        if ($this->label) {
            $pos = $pos ?? new Point(2300, $this->pos()->y());
            $draw->withFillColor('white', function (Draw $draw) use ($pos): void {
                $draw->text($pos, ' ' . $this->label() . ' ', '#808080');
            });
        }
    }
}
