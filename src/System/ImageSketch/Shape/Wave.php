<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\Shape;

use Edutiek\AssessmentService\System\ImageSketch\Draw;
use Edutiek\AssessmentService\System\ImageSketch\Point;
use Closure;

class Wave extends NoShape
{
    private Point $end;

    public const LENGTH = 50;
    public const HEIGHT = 50;
    public const LINE_WIDTH = 10;

    public function __construct(Point $end, ...$args)
    {
        $this->end = $end;
        parent::__construct(...$args);
    }

    public function draw(Draw $draw): void
    {
        $pitch = new Point(Wave::LENGTH, Wave::HEIGHT);
        $length = (int) sqrt(pow($this->end->x(), 2) + pow($this->end->y(), 2));
        $waves = (int) floor($length / $pitch->x());

        $path = $this->wavePath($draw, $pitch, $waves);

        $rest = $length % $pitch->x();
        if ($rest !== 0) {
            $path[] = $this->drawWaveRest($draw, $pitch, $rest, $waves);
        }

        $angle = $this->angle($this->end);
        $origin = $this->rotate(
            $this->pos(),
            -$angle
        );

        $draw->with([
            'rotation' => $angle,
            'strokeColor' => $this->color(),
            'strokeWidth' => self::LINE_WIDTH,
            'originAt' => $origin,
        ], fn () => $draw->path(new Point(0, 0), $path));

        $this->drawLabel($draw);
    }

    private function drawWaveRest(Draw $draw, Point $pitch, float $rest, int $waves): Closure
    {
        /*
          The function of a quadratic bezier curve is P(t) = (1 - t)^2 * P  + 2t(1 - t) * P  + t^2 * P with 0 <= t <= 1 and P(0) = P  and P(1) = P .
                                                                          0                1          2                             0             2
          P  is the control point, indicating where the curve converges.
           1
          For our use case we can use the following points:
          P  = (0, 0)
           0
          P  = (0.5b, c)
           1
          P  = (b, 0)
           2
          b is the desired length of one wave (WAVE::LENGTH).
          c is the desired height for the control point (WAVE:HEIGHT), the actual max height of the curve will be c / 2.
          We use a as the ratio (gradient) between b c, so c / b.
          Additionally we are only interested in the y value of formular.
          This reduces the quadratic bezier curve to the following:
          2x - 2x^2 and with a to change the ratio of the curve we have:

          f(x) = a * 2x - a * 2x^2

          The gradient at f(0) is g(x) = a * 2x.
          To offset the gradient graph at x = d to d we have h(x) = f'(d)(x - d) + f(d).
          The control point for d is the intersection point (see A) g(x) = h(x) = d / 2.

          |    h(x)   g(x)
          |     \    /
          |      \  /
          |       A/
          |       /\
          |      /  \
          |     /    \
          |    /  -----
    f(d) -|   / -/     \-
          |  / /  Wave   \
          | / /   f(x)    \
          |/ /              \
          0---------|--|-----|--
          |        0.5 d    1.0

        */

        $gradient = $pitch->y() / $pitch->x(); // a = c / b

        $end_of_last_wave = new Point($waves * $pitch->x(), 0);
        $x_procent = $rest / $pitch->x();
        // f(x) = a * 2x - a * 2x^2
        $y_procent = $gradient * 2 * $x_procent - $gradient * 2 * pow($x_procent, 2);

        $control_x = $x_procent / 2; // g(x) = h(x) = d / 2 with d = x_procent.
        $control_y = 2 * $control_x * $gradient; // g(control_x)

        $negate = $waves & 1 ? -1 : 1; // Wave up or down.
        // $rel_to_abs = fn ($a) => $a * $pitch->x(); // Percent to desired length in the image.
        // Percent to desired length in the image.
        $rel_to_abs = static function ($a) use ($pitch): float {
            return $a * $pitch->x();
        };

        $control = new Point($rel_to_abs($control_x), $negate * -$rel_to_abs($control_y));
        $pos = new Point($rest, $negate * -$rel_to_abs($y_procent));

        return $this->quadraticCurve(
            $draw,
            $draw->shiftBy($control, $end_of_last_wave),
            $draw->shiftBy($pos, $end_of_last_wave)
        );
    }

    private function quadraticCurve(Draw $draw, Point $control, Point $pos): Closure
    {
        // The control point of Draw::quadraticCurve is relative to $pos but we have an absolute control point.
        $negated = new Point(-$pos->x(), -$pos->y());
        $control = $draw->shiftBy($control, $negated);
        return $draw->quadraticCurve($control, $pos);
    }

    /**
     * @return list<Closure>
     */
    private function wavePath(Draw $draw, Point $pitch, int $waves): array
    {
        return array_map(function (int $i) use ($draw, $pitch): Closure {
            $curve = new Point(- $pitch->x() / 2, - $pitch->y() * ($i & 1 ? -1 : 1));
            return $draw->quadraticCurve($curve, new Point(($i + 1) * $pitch->x(), 0));
        }, $this->range($waves));
    }

    private function range(int $length): array
    {
        return $length > 0 ? range(0, $length -1) : [];
    }

    private function relativePos(Point $pos, Point $direction): Point
    {
        return $this->rotate($pos, -$this->angle($direction));
    }

    private function angle(Point $direction): float
    {
        if ($direction->x() > 0) {
            return atan($direction->y() / $direction->x());
        }
        else if ($direction->x() < 0) {
            return pi() + atan($direction->y() / $direction->x());
        }
        else {
            return ($direction->y() <=> 0) * pi()/2;
        }
    }

    private function rotate(Point $a, float $r): Point
    {
        return new Point(
            $a->x() * cos($r) - $a->y() * sin($r),
            $a->x() * sin($r) + $a->y() * cos($r)
        );
    }
}
