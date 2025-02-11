<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch;

interface FullService
{
    /**
     * Draws the shapes onto a copy of the image.
     *
     * @param Shape[] $shapes
     * @param resource $image
     * @return resource
     */
    public function applyShapes(array $shapes, $image);
}