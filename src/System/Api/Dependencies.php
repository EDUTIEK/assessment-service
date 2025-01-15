<?php

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Data\Repository;

Interface Dependencies
{
    public function configRepo() : Repository;
}