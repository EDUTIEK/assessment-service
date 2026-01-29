<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Alert;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Api\ApiException;
use Edutiek\AssessmentService\Assessment\Data\Alert;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function all(): array
    {
        return $this->repos->alert()->allByAssId($this->ass_id);
    }

    public function new(): Alert
    {
        return $this->repos->alert()->new()->setAssId($this->ass_id);
    }

    public function create(Alert $alert): void
    {
        $this->checkScope($alert);
        $this->repos->alert()->create($alert);
    }

    public function one(int $id): ?Alert
    {
        $alert = $this->repos->alert()->one($id);
        if ($alert !== null) {
            $this->checkScope($alert);
            return $alert;
        }
        return null;
    }

    public function delete(Alert $alert): void
    {
        $this->checkScope($alert);
        $this->repos->alert()->delete($alert->getId());
    }

    private function checkScope(Alert $alert)
    {
        if ($alert->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}
