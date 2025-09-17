<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\DisabledGroup;

use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\DisabledGroup;
use Edutiek\AssessmentService\Assessment\Data\DisabledGroupRepo;

class Service implements FullService
{
    public function __construct(
        private readonly int $ass_id,
        private readonly DisabledGroupRepo $repo
    ) {
    }

    public function new(): DisabledGroup
    {
        return $this->repo->new()->setAssId($this->ass_id);
    }

    public function all(): array
    {
        return $this->repo->allByAssId($this->ass_id);
    }

    public function save(DisabledGroup $group): void
    {
        $this->checkScope($group);
        $this->repo->save($group);
    }

    public function saveAll(array $groups): void
    {
        $this->repo->deleteByAssId($this->ass_id);
        $insert = []; // Remove duplicates.
        foreach ($groups as $group) {
            if (is_string($group)) {
                $group = $this->repo->new()->setName($group)->setAssId($this->ass_id);
            }
            $insert[$group->getName()] = $group;
        }

        array_map($this->save(...), $insert);
    }

    private function checkScope(DisabledGroup $group)
    {
        if ($group->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}
