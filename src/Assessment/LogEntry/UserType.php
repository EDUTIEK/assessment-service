<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

enum UserType
{
    case System;
    case Writer;
    case Corrector;
}
