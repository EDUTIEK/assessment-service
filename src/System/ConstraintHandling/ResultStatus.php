<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

enum ResultStatus: string
{
    case OK = 'ok';
    case ASK = 'ask';
    case BLOCK = 'block';
}
