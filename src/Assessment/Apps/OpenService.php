<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\System\Config\Frontend;

interface OpenService
{
    /**
     * Open a frontend for writing
     */
    public function open(Frontend $frontend, int $context_id, string $return_url): void;
}
