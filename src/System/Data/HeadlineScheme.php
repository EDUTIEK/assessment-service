<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

enum HeadlineScheme: string
{
    case SINGLE = 'single';
    case THREE = 'three';
    case NUMERIC = 'numeric';
    case EDUTIEK = 'edutiek';

    public function class(): string
    {
        return match($this) {
            self::SINGLE => 'headlines-single',
            self::THREE => 'headlines-three',
            self::NUMERIC => 'headlines-numeric',
            self::EDUTIEK => 'headlines-edutiek',
        };
    }
}
