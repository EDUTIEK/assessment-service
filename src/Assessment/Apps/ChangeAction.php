<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

enum ChangeAction: string
{
    case SAVE = 'save';
    case DELETE = 'delete';
}
