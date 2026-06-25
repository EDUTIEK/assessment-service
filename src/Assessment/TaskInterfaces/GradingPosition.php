<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;

enum GradingPosition: int
{
    /**
     * First corrector
     */
    case FIRST = 0;

    /**
     * SEcond Corrector
     */
    // no break
    case SECOND = 1;

    /**
     * Stitch decider
     */
    // no break
    case STITCH = 2;

    public static function all()
    {
        return [
            GradingPosition::FIRST,
            GradingPosition::SECOND,
            GradingPosition::STITCH
        ];
    }

    /**
     * Get the order of two grading postions
     * @return int -1 if position1 < position2, 0 if position1 = position2, 1 if Position1 > position2
     */
    public static function order(GradingPosition $position1, GradingPosition $position2)
    {
        return $position1->value <=> $position2->value;
    }

    public static function required(int $required_correctors)
    {
        switch ($required_correctors) {
            case 2:
                return [
                    GradingPosition::FIRST,
                    GradingPosition::SECOND
                ];
            default:
                return [
                    GradingPosition::FIRST,
                ];
        }
    }

    public function languageVariable(): string
    {
        return match ($this) {
            self::FIRST => 'grading_pos_first',
            self::SECOND => 'grading_pos_second',
            self::STITCH => 'grading_pos_stitch',
        };
    }

    public function initialsLanguageVariable(): string
    {
        return match ($this) {
            self::FIRST => 'grading_pos_first_short',
            self::SECOND => 'grading_pos_second_short',
            self::STITCH => 'grading_pos_stitch_short',
        };
    }

    public function isCorrector()
    {
        return $this !== self::STITCH;
    }

    public function isStitch()
    {
        return $this === self::STITCH;
    }

    public function canEnterRevisionText(CorrectionProcedure $procedure): bool
    {
        return match ($procedure) {
            CorrectionProcedure::APPROXIMATION => true,
            CorrectionProcedure::CONSULTING => $this === self::SECOND,
            CorrectionProcedure::NONE => false
        };
    }

}
