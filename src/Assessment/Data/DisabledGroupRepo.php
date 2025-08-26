<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface DisabledGroupRepo
{
    /**
     * @return DisabledGroup[]
     */
    public function get(int $ass_id): array;

    /**
     * @param string[]|DisabledGroup[] $groups
     */
    public function save(int $ass_id, array $groups): void;
}
