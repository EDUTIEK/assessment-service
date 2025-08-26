<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\DisabledGroup;

use Edutiek\AssessmentService\Assessment\Data\DisabledGroup;

interface FullService
{
    /**
     * @return DisabledGroup[]
     */
    public function get(): array;

    /**
     * @param string[]|DisabledGroup[] $groups
     */
    public function save(array $groups): void;
}
