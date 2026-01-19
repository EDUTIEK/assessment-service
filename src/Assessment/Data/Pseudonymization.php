<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum Pseudonymization: string
{
    case WRITER_ID = 'writer_id';
    case USER_ID = 'user_id';
    case LOGIN = 'login';
    case MATRICULATION = 'matriculation';
    case NAME = 'name';
}
