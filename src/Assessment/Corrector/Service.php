<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Corrector;

use Edutiek\AssessmentService\Assessment\Corrector\FullService;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Repositories;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function hasReports(): bool
    {
        return $this->repos->corrector()->hasReports();
    }

    public function all(): array
    {
        return $this->repos->corrector()->allByAssId($this->ass_id);
    }

    public function has(int $corrector_id): bool
    {
        return $this->repos->corrector()->hasByCorrectorIdAndAssId($corrector_id, $this->ass_id);
    }

    public function oneByUserId(int $user_id): ?Corrector
    {
        return $this->repos->corrector()->oneByUserIdAndAssId($user_id, $this->ass_id);
    }

    public function oneById(int $corrector_id): ?Corrector
    {
        return $this->repos->corrector()->one($corrector_id);
    }

}

