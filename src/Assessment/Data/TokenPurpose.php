<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum TokenPurpose: string
{
    /**
     * Data Tokens are replaced with each backend request
     */
    case DATA = 'data';

    /**
     * File Tokens are valid for a longer period
     */
    case FILE = 'file';
}
