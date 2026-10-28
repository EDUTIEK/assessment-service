<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum TokenPurpose: string
{
    /**
     * Login tokens are used by AppStart
     * They are not bound to an assessment (ass_id is 0)
     */
    case LOGIN = 'login';

    /**
     * Data Tokens are replaced with each backend request
     */
    case DATA = 'data';

    /**
     * File Tokens are valid for a longer period
     */
    case FILE = 'file';
}
