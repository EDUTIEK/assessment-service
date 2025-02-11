<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\Shape;

use Edutiek\AssessmentService\System\ImageSketch\Draw;
use Edutiek\AssessmentService\System\ImageSketch\Point;

class Line extends NoShape
{
    private Point $end;

    public const LINE_WIDTH = 10;

    public function __construct(Point $end, ...$args)
    {
        $this->end = $end;
        parent::__construct(...$args);
    }

    public function draw(Draw $draw): void
    {
        $draw->with([
            'strokeColor' => $this->color(),
            'strokeWidth' => self::LINE_WIDTH,
        ], fn () => $draw->polygon([$this->pos(), $draw->shiftBy($this->pos(), $this->end)]));

        $this->drawLabel($draw);
    }
}
