<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

enum TaskType: string
{
    case ESSAY = 'essay';

    public function component(): string
    {
        return match ($this) {
            self::ESSAY => 'EssayTask',
        };
    }
}
