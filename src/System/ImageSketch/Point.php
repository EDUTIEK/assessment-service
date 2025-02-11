<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch;

class Point
{
    private float $x;
    private float $y;

    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function x(): float
    {
        return $this->x;
    }

    public function y(): float
    {
        return $this->y;
    }
}
