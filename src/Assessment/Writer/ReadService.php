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

    /** @return int[] */
    public function allIds(): array;
    /**
     * Get the ids of writers that can be corrected
     * - They must be authorized and not excluded
     * @return int[]
     */
    public function correctableIds(): array;
    public function oneByWriterId(int $writer_id): ?Writer;
    public function hasStitchDecisions(): bool;
}
