<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\System\Config\Frontend;

interface OpenService
{
    /**
     * Open a frontend for writing
     */
    public function openWriter(int $context_id, string $return_url): void;
    public function openCorrector(int $context_id, string $return_url, ?int $assignment_id): void;
}
