<?php

namespace Edutiek\AssessmentService\Assessment\Writer;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

interface ReadService
{
    public function has(int $writer_id): bool;
    public function oneByUserId(int $user_id): ?Writer;
    /** @return Writer[] */
    public function all(): array;
    public function oneByWriterId(int $writer_id): ?Writer;
    public function hasStitchDecisions(): bool;
}