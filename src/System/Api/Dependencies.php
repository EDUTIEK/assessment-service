<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Data\Repository;

Interface Dependencies
{
    public function configRepo() : Repository;
}
