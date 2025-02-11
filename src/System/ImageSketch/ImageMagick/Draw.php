<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\ImageMagick;

use Edutiek\AssessmentService\System\ImageSketch\Draw as DrawInterface;
use ImagickDraw;
use ImagickPixel;
use Imagick;
use Edutiek\AssessmentService\System\ImageSketch\Point;
use Closure;

/**
 * @implements DrawInterface<ImagickDraw>
 */
class Draw implements DrawInterface
{
    private ImagickDraw $magic;
    private Imagick $parent;
    private Closure $shift_text;
    private Point $origin;

    public function __construct(ImagickDraw $magic, Imagick $parent)
    {
        $this->magic = $magic;
        $this->parent = $parent;
        $this->shift_text = fn (Point $pos): Point => $pos;
        $this->magic->setTextEncoding('UTF-8');
        $this->origin = new Point(0, 0);
    }

    public function path(Point $start, array $segments): void
    {
        $this->withPath(function () use ($start, $segments): void {
            $this->magic->pathMoveToAbsolute($this->origin->x() + $start->x(), $this->origin->y() + $start->y());
            foreach ($segments as $segment) {
                $segment($this->magic);
            }
        });
    }

    public function lineTo(Point $point): Closure
    {
        return function (ImagickDraw $magic) use ($point): void {
            $magic->pathLineToAbsolute($this->origin->x() + $point->x(), $this->origin->y() + $point->y());
        };
    }

    public function quadraticCurve(Point $control, Point $point): Closure
    {
        return function (ImagickDraw $magic) use ($control, $point): void {
            $magic->pathCurveToQuadraticBezierAbsolute(
                $this->origin->x() + $point->x() + $control->x(),
                $this->origin->y() + $point->y() + $control->y(),
                $this->origin->x() + $point->x(),
                $this->origin->y() + $point->y()
            );
        };
    }

    public function withRotation(float $angle, callable $within): void
    {
        $degrees = $angle * 180 / pi();
        $this->magic->rotate($degrees);
        $within($this);
        $this->magic->rotate(-$degrees);
    }


    public function polygon(array $points): void
    {
        if ([] === $points) {
            return;
        }
        $this->path($points[0], array_map([$this, 'lineTo'], array_slice($points, 1)));
    }

    public function circle(Point $center, float $radius): void
    {
        $this->magic->circle(
            $this->origin->x() + $center->x() -1,
            $this->origin->y() + $center->y() -1,
            $this->origin->x() + $center->x() + $radius -1,
            $this->origin->y() + $center->y() + $radius -1
        );
    }

    public function with(array $with_what, callable $within): void
    {
        $with_what = array_map(
            fn(string $k, $v) => ['with' . ucfirst($k), $v],
            array_keys($with_what),
            array_values($with_what)
        );

        $proc = array_reduce(
            $with_what,
            fn(callable $prev, array $with_what) => fn() => [$this, $with_what[0]]($with_what[1], $prev),
            $within
        );

        $proc($this);
    }

    public function withFillColor(string $color, callable $within): void
    {
        $this->withChange('getFillColor', 'setFillColor', $color, $within);
    }

    public function withStrokeColor(string $color, callable $within): void
    {
        $this->withChange('getStrokeColor', 'setStrokeColor', $color, $within);
    }

    public function withStrokeWidth(float $width, callable $within): void
    {
        $this->withChange('getStrokeWidth', 'setStrokeWidth', $width, $within);
    }

    public function withOriginAt(Point $origin, callable $within): void
    {
        $old = $this->origin;
        $this->origin = $origin;
        $within($this);
        $this->origin = $old;
    }

    public function shiftBy(Point $by, Point $point): Point
    {
        return new Point($by->x() + $point->x(), $by->y() + $point->y());
    }

    public function shiftAllBy(Point $by, array $points): array
    {
        return array_map(fn($p) => $this->shiftBy($by, $p), $points);
    }

    public function text(Point $pos, string $text, ?string $background_color = null): void
    {
        $run = $background_color === null ?
               fn ($f) => $f($this) :
               fn ($f) => $this->withChange('getTextUnderColor', 'setTextUnderColor', $background_color, $f);
        $pos = ($this->shift_text)($pos, $text);
        $run(fn () => $this->magic->annotation($pos->x(), $pos->y(), $text));
    }

    public function withCenteredText(callable $within): void
    {
        $old = $this->shift_text;

        $this->shift_text = fn (Point $pos, string $text): Point => $this->shiftBy(
            new Point(0, $this->parent->queryFontMetrics($this->magic, $text, false)['ascender'] / 2),
            $pos
        );

        $this->withChange('getTextAlignment', 'setTextAlignment', Imagick::ALIGN_CENTER, $within);
        $this->shift_text = $old;
    }

    public function withFontSize(int $font_size, callable $within): void
    {
        $this->withChange('getFontSize', 'setFontSize', $font_size, $within);
    }

    private function withPath(callable $proc): void
    {
        $this->magic->pathStart();
        $proc();
        $this->magic->pathFinish();
    }

    private function withChange(string $get, string $set, $val, callable $within): void
    {
        $old = $this->magic->$get();
        $this->magic->$set($val);
        $within($this);
        $this->magic->$set($old);
    }
}
