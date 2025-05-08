<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Entity;

enum KeyCase: string
{
    case PASCAL_CASE = 'pascal_case';
    case SNAKE_CASE = 'snake_case';
}
