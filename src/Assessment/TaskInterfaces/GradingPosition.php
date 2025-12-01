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
    case SECOND = 1;

    /**
     * Stitch decider
     */
    case STITCH = 2;

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
