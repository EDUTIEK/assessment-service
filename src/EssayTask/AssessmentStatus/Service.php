<?php

namespace Edutiek\AssessmentService\EssayTask\AssessmentStatus;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }
    public function hasComments()
    {
        return $this->repos->correctorComment()->hasByAssId($this->ass_id);
    }

    public function hasAuthorizedSummaries(?int $corrector_id = null)
    {
        return $this->repos->correctorSummary()->hasAuthorizedByAssId($this->ass_id, $corrector_id);
    }
}
