<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\ImageProcessing;

use Edutiek\AssessmentService\EssayTask\Data\CorrectionMark;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\ImageSketch\FullService as ImageSketchService;
use Edutiek\AssessmentService\System\ImageSketch\Point;
use Edutiek\AssessmentService\System\ImageSketch\Shape;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;

class Service implements FullService
{
    private const FILL_NORMAL = '#3365ff40';
    private const BORDER_NORMAL = '#3365ff';

    public function __construct(
        private readonly ImageSketchService $sketch
    ) {
    }


    public function applyCommentsMarks(int $page_number, ImageDescriptor $image, array $comments): ImageDescriptor
    {
        $shapes = [];
        foreach ($comments as $comment) {
            if ($comment->getParentNumber() == $page_number && !empty($comment->getMarks())) {
                foreach ($comment->getMarks() as $mark) {
                    $filled = in_array($mark->getShape(), CorrectionMark::FILLED_SHAPES);
                    if ($filled) {
                        $shapes[] = $this->getShapeFromMark($mark, $comment->getLabel(), $this->getMarkFillColor($comment));
                    } else {
                        $shapes[] = $this->getShapeFromMark($mark, $comment->getLabel(), $this->getMarkBorderColor($comment));
                    }
                }
            }
        }
        if (!empty($shapes)) {
            $sketched = $this->sketch->applyShapes($shapes, $image->stream());
            return new ImageDescriptor($sketched, $image->width(), $image->height(), 'image/jpeg');
        } else {
            return $image;
        }

    }

    /**
     * Get the fill color for a graphical mark
     * todo: use corrector colors
     */
    private function getMarkFillColor(CorrectorComment $comment): string
    {
        return self::FILL_NORMAL;
    }

    /**
     * Get the border color for a graphical mark
     *  todo: use corrector colors
     */
    private function getMarkBorderColor(CorrectorComment $comment): string
    {
        return self::BORDER_NORMAL;
    }

    /**
     * Get the image sketcher shape from a correction mark
     */
    private function getShapeFromMark(CorrectionMark $mark, string $label, string $color): Shape
    {
        $pos = new Point($mark->getPos()->getX(), $mark->getPos()->getY());

        switch ($mark->getShape()) {
            case CorrectionMark::SHAPE_LINE:
                $end = new Point($mark->getEnd()->getX(), $mark->getEnd()->getY());
                return new Shape\Line($end, $pos, $label, $color);

            case CorrectionMark::SHAPE_WAVE:
                $end = new Point($mark->getEnd()->getX(), $mark->getEnd()->getY());
                return new Shape\Wave($end, $pos, $label, $color);

            case CorrectionMark::SHAPE_RECTANGLE:
                $width = $mark->getWidth();
                $height = $mark->getHeight();
                return new Shape\Rectangle($width, $height, $pos, $label, $color);

            case CorrectionMark::SHAPE_POLYGON:
                $points = [];
                foreach ($mark->getPolygon() as $point) {
                    $points[] = new Point($point->getX(), $point->getY());
                }
                return new Shape\Polygon($points, $pos, $label, $color);

            case CorrectionMark::SHAPE_CIRCLE:
            default:
                return new Shape\Circle($this->getShapeSymbol($mark), '#000000', 80, $pos, $label, $color);
        }
    }

    /**
     * Get a mark symbol that is known to the image sketching font
     */
    private function getShapeSymbol(CorrectionMark $mark): string
    {
        switch ($mark->getSymbol()) {
            case '✓':
                return '√';
            case '✗':
                return 'X';
            default:
                return $mark->getSymbol();
        }
    }
}
