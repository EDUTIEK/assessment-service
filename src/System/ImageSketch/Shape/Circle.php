<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ImageSketch\Shape;

use Edutiek\AssessmentService\System\ImageSketch\Point;
use Edutiek\AssessmentService\System\ImageSketch\Draw;

class Circle extends NoShape
{
    private string $symbol;
    private string $symbol_color;
    private int $font_size;

    public function __construct(string $symbol, string $symbol_color, int $font_size, ...$args)
    {
        $this->symbol = $symbol;
        $this->symbol_color = $symbol_color;
        $this->font_size = $font_size;
        parent::__construct(...$args);
    }

    public function draw(Draw $draw): void
    {
        $draw->withFillColor($this->color(), function (Draw $draw): void {
            $draw->circle($this->pos(), 50);
        });

        $draw->withFillColor($this->symbol_color, function (Draw $draw): void {
            $draw->withCenteredText(function (Draw $draw): void {
                $draw->withFontSize($this->font_size, function (Draw $draw): void {
                    $draw->text(new Point($this->pos()->x(), $this->pos()->y() - 10), $this->symbol);
                });
            });
        });
        $this->drawLabel($draw, new Point($this->pos()->x(), $this->pos()->y() - 120));
    }
}
