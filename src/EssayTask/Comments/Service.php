<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Comments;

use Edutiek\AssessmentService\EssayTask\Comments\FullService;
use Edutiek\AssessmentService\EssayTask\Data\CommentRating;
use Edutiek\AssessmentService\EssayTask\Data\CorrectionSettings;
use Edutiek\AssessmentService\EssayTask\Data\CorrectorComment;
use Edutiek\AssessmentService\EssayTask\Data\CorrectionMark;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\ImageSketch\FullService as ImageSketchService;
use Edutiek\AssessmentService\System\ImageSketch\Point;
use Edutiek\AssessmentService\System\ImageSketch\Shape;

class Service
{
    private const BACKGROUND_NORMAL = '#D8E5F4';
    private const BACKGROUND_EXCELLENT = '#E3EFDD';
    private const BACKGROUND_CARDINAL = '#FBDED1';

    private const FILL_NORMAL = '#3365ff40';
    private const FILL_EXCELLENT = '#19e62e40';
    private const FILL_CARDINAL = '#bc471040';

    private const BORDER_NORMAL = '#3365ff';
    private const BORDER_EXCELLENT = '#19e62e';
    private const BORDER_CARDINAL = '#bc4710';

    public function __construct(
        private readonly ImageSketchService $sketch
    ) {}

    public function getCommentsHtml(array $comments, CorrectionSettings $settings) : string
    {
        $html = '';
        foreach ($comments as $comment) {
            if ($comment->hasDetailsToShow()) {
                $content = $comment->getLabel();
                if ($comment->showRating())

                    $content = $comment->getLabel();
                if ($comment->showRating() && $comment->getRating() == CommentRating::CARDINAL->value) {
                    $content .= ' ' . $settings->getNegativeRating();
                }
                if ($comment->showRating() && $comment->getRating() == CommentRating::EXCELLENT->value) {
                    $content .= ' ' . $settings->getPositiveRating();
                }

                $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');

                $color = $this->getTextBackgroundColor([$comment]);
                $content = '<strong style="background-color:'. $color . ';">' . $content . '</strong>';

                if (!empty($comment->getComment())) {
                    $content .= ' ' . htmlspecialchars($comment->getComment(), ENT_NOQUOTES, 'UTF-8');
                }

                if ($comment->showPoints() && $comment->getPoints() == 1) {
                    $content .= '<br />(1 Punkt)';
                }
                elseif ($comment->showPoints() && $comment->getPoints() != 0) {
                    $content .= '<br />(' . $comment->getPoints() . ' Punkte)';
                }

                $content = '<p style="font-family: sans-serif; font-size:10px;">' . $content . '</p>';

                $html .= $content . "\n";

                // remove ascii control characters except tab, cr and lf
                $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $html);
            }
        }
        return $html;
    }

    public function getSortedCommentsOfParent(array $comments, int $parent_no) : array
    {
        $sort = [];
        foreach($comments as $comment) {
            if ($comment->getParentNumber() == $parent_no) {
                $key = sprintf('%06d', $comment->getStartPosition()) . $comment->getKey();
                $sort[$key] = $comment;
            }
        }
        ksort($sort);

        $result = [];
        $number = 1;
        foreach ($sort as $comment) {
            // only comments with details to show should get a label
            // others are only marks in the text
            if ($comment->hasDetailsToShow()) {
                $result[] = $comment->withLabel($parent_no . '.' . $number++);
            }
            else {
                $result[] = $comment;
            }
        }

        return $result;
    }

    public function applyCommentsMarks(int $page_number, ImageDescriptor $image, array $comments) : ImageDescriptor
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
            return new ImageDescriptor($sketched, 'image/jpeg', $image->width(), $image->height());
        }
        else {
            return $image;
        }

    }

    public function getTextBackgroundColor(array $comments) : string
    {
        $color = '';
        foreach ($comments as $comment) {
            if ($comment->showRating() && $comment->getRating() == CommentRating::CARDINAL->value) {
                $color = self::BACKGROUND_CARDINAL;
            }
            elseif ($comment->showRating() && $comment->getRating() == CommentRating::EXCELLENT->value) {
                $color = self::BACKGROUND_EXCELLENT;
            }
            else if ($color == '') {
                $color = self::BACKGROUND_NORMAL;
            }
        }
        return $color;
    }

    /**
     * Get the fill color for a graphical mark
     */
    private function getMarkFillColor(CorrectorComment $comment) : string
    {
        if ($comment->showRating() && $comment->getRating() ==  CommentRating::CARDINAL->value) {
            return self::FILL_CARDINAL;
        }
        elseif ($comment->showRating() && $comment->getRating() == CommentRating::EXCELLENT->value) {
            return self::FILL_EXCELLENT;
        }
        else {
            return self::FILL_NORMAL;
        }
    }

    /**
     * Get the border color for a graphical mark
     */
    private function getMarkBorderColor(CorrectorComment $comment) : string
    {
        if ($comment->showRating() && $comment->getRating() ==  CommentRating::CARDINAL->value) {
            return self::BORDER_CARDINAL;
        }
        elseif ($comment->showRating() && $comment->getRating() == CommentRating::EXCELLENT->value) {
            return self::BORDER_EXCELLENT;
        }
        else {
            return self::BORDER_NORMAL;
        }
    }

    /**
     * Get the image sketcher shape from a correction mark
     */
    private function getShapeFromMark(CorrectionMark $mark, string $label, string $color) : Shape
    {
        $pos = new Point($mark->getPos()->getX(), $mark->getPos()->getY());

        switch($mark->getShape()) {
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
                foreach($mark->getPolygon() as $point) {
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
    private function getShapeSymbol(CorrectionMark $mark) : string
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