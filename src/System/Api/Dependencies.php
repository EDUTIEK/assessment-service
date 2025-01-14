<?php

namespace Edutiek\AssessmentService\System\Api;

use  Edutiek\AssessmentService\System\Config\Repository;

Interface Dependencies
{
    public function configRepo() : Repository;
}