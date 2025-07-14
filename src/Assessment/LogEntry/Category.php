<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

enum Category : string
{
    case AUTHORIZE = "authorize";
    case NOTE = "note";
    case WORKING_TIME = "working_time";
    case EXCLUSION = "exclusion";
}
