<?php

namespace Edutiek\AssessmentService\Views\Data;

enum ClientFilterOptions: string
{
    case ONLINE = 'online';
    case OFFLINE = 'offline';
    case LOW_BATTERY = 'low_battery';
    case HIDDEN = 'hidden';
    case MULTI_SESSIONS = 'multi_sessions';
}
