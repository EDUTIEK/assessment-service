<?php

namespace Edutiek\AssessmentService\Assessment\Writer;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

interface ReadService
{
    public function has(int $writer_id): bool;
    public function oneByUserId(int $user_id): ?Writer;
}