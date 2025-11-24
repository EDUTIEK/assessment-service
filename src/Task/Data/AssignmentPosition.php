<?php

namespace Edutiek\AssessmentService\Task\Data;

enum AssignmentPosition: int
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
            self::FIRST => 'assignment_pos_first',
            self::SECOND => 'assignment_pos_second',
            self::STITCH => 'assignment_pos_stitch',
        };
    }

    public function initialsLanguageVariable(): string
    {
        return match ($this) {
            self::FIRST => 'assignment_pos_first_short',
            self::SECOND => 'assignment_pos_second_short',
            self::STITCH => 'assignment_pos_stitch_short',
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
