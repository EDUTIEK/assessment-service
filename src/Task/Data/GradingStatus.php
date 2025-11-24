<?php

namespace Edutiek\AssessmentService\Task\Data;

enum GradingStatus: string
{
    case NOT_STARTED = "not_started";
    case OPEN = "open";
    case PRE_GRADED = "pre_graded";
    case AUTHORIZED = "authorized";
    case REVISED = "revised";

    public function isToCorrect(): bool
    {
        return $this === self::NOT_STARTED || $this === self::OPEN;
    }

    public function isToAuthorize(): bool
    {
        return $this === self::OPEN || $this === self::PRE_GRADED;
    }
}
