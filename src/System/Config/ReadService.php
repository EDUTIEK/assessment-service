<?php

namespace Edutiek\AssessmentService\System\Config;

interface ReadService
{
    public function readConfig(): Config;
}