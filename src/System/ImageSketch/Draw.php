<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch;

use Closure;

/**
 * @template A
 */
interface Draw
{
    /**
     * @param list<Closure(A): void>
     */
    public function path(Point $start, array $segments): void;

    /**
     * @return Closure(A): void
     */
    public function lineTo(Point $point): Closure;

    /**
     * @return Closure(A): void
     */
    public function quadraticCurve(Point $control, Point $point): Closure;

    /**
     * Parameter $angle is in radiants.
     */
    public function withRotation(float $angle, callable $within): void;

    /**
     * @param list<Point> $points
     */
    public function polygon(array $points): void;

    /**
     * @param Point<list> $points
     * @return Point<list>
     */
    public function shiftAllBy(Point $by, array $points): array;
    public function circle(Point $center, float $radius): void;
    public function with(array $with_what, callable $within): void;
    public function withFillColor(string $color, callable $within): void;
    public function withStrokeColor(string $color, callable $within): void;
    public function withStrokeWidth(float $width, callable $within): void;
    public function withOriginAt(Point $origin, callable $within): void;
    public function shiftBy(Point $by, Point $point): Point;
    public function text(Point $pos, string $text): void;
    public function withCenteredText(callable $within): void;
    public function withFontSize(int $font_size, callable $within): void;
}
