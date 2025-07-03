<?php

namespace Edutiek\AssessmentService\Assessment\Writer;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Writer;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos
    ) {
    }

    public function has(int $writer_id): bool
    {
        return $this->repos->writer()->hasByWriterIdAndAssId($writer_id, $this->ass_id);
    }

    public function oneByUserId(int $user_id) : ?Writer
    {
        return $this->repos->writer()->oneByUserIdAndAssId($user_id, $this->ass_id);
    }
}