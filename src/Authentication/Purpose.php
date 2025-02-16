<?php

namespace Edutiek\AssessmentService\Assessment\Authentication;

enum Purpose: string
{
    case PURPOSE_DATA = 'data';
    case PURPOSE_FILE = 'file';
}
