<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\DisabledGroup;

use Edutiek\AssessmentService\Assessment\Data\DisabledGroup;

interface FullService
{
    public function new(): DisabledGroup;

    /**
     * @return DisabledGroup[]
     */
    public function all(): array;

    public function save(DisabledGroup $group): void;

    /**
     * @param string[]|DisabledGroup[] $groups
     */
    public function saveAll(array $groups): void;
}
