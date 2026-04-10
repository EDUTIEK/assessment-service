<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

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
}
