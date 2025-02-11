<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch;

/**
 * @template A
 */
interface Shape
{
    /**
     * @param Draw<A> $draw
     */
    public function draw(Draw $draw): void;
    public function pos(): Point;
    public function label(): string;
    public function color(): string;
}
