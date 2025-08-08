<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Corrector;

use Edutiek\AssessmentService\Assessment\Corrector\FullService;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Api\ApiException;

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
        $corrector = $this->repos->corrector()->oneByUserIdAndAssId($user_id, $this->ass_id);
        return $corrector;
    }

    public function oneById(int $corrector_id): ?Corrector
    {
        $corrector = $this->repos->corrector()->one($corrector_id);
        $this->checkScope($corrector);
        return $corrector;
    }

    public function remove(Corrector $corrector)
    {
        $this->checkScope($corrector);
        $this->repos->corrector()->delete($corrector->getId());
    }
    private function checkScope(Corrector $corrector)
    {
        if ($corrector->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }

    public function getByUserId(int $user_id): Corrector
    {
        $corrector = $this->oneByUserId($user_id);
        if ($corrector === null) {
            $corrector = $this->repos->corrector()->new()->setAssId($this->ass_id)->setUserId($user_id);
            $this->repos->corrector()->save($corrector);
        } else {
            $this->checkScope($corrector);
        }
        return $corrector;
    }
}

