<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface DisabledGroupRepo
{
    public function new(): DisabledGroup;

    /**
     * @return DisabledGroup[]
     */
    public function allByAssId(int $ass_id): array;

    public function save(DisabledGroup $group): void;

    public function deleteByAssId(int $ass_id): void;
}
