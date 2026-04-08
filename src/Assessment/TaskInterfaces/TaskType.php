<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

enum TaskType: string
{
    case ESSAY = 'essay';

    public static function all(): array
    {
        return [self::ESSAY];
    }

    public function component(): string
    {
        return match ($this) {
            self::ESSAY => 'EssayTask',
        };
    }
}
