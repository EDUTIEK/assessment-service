<?php

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\System\Data\Result;

interface CronHandler
{
    public function run(): Result;
}
