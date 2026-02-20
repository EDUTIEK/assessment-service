<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface ContextInfoRepo
{
    public function get(int $context_id): ContextInfo;
}
