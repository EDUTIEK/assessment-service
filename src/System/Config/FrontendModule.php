<?php

namespace Edutiek\AssessmentService\System\Config;

enum FrontendModule: string
{
    case WRITER = 'assessment-writer';
    case CORRECTOR = 'assessment-corrector';
}