<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\DisabledGroup;

use Edutiek\AssessmentService\Assessment\Data\DisabledGroupRepo;

class Service implements FullService
{
    public function __construct(
        private readonly int $ass_id,
        private readonly DisabledGroupRepo $repo
    )
    {
    }

    public function get(): array
    {
        return $this->repo->get($this->ass_id);
    }

    public function save(array $groups): void
    {
        $this->repo->save($this->ass_id, $groups);
    }
}
