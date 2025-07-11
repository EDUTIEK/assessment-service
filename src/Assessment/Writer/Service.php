<?php

namespace Edutiek\AssessmentService\Assessment\Writer;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Api\ApiException;

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

    public function getByUserId(int $user_id) : Writer
    {
        $writer = $this->oneByUserId($user_id);
        if ($writer === null) {
            $writer = $this->repos->writer()->new()->setAssId($this->ass_id)->setUserId($user_id);
            $this->repos->writer()->save($writer);
        } else {
            $this->checkScope($writer);
        }
        return $writer;
    }

    public function oneByUserId(int $user_id) : ?Writer
    {
        return $this->repos->writer()->oneByUserIdAndAssId($user_id, $this->ass_id);
    }

    public function oneByWriterId(int $writer_id): ?Writer
    {
        $writer = $this->repos->writer()->one($writer_id);
        $this->checkScope($writer);
        return $writer;
    }

    public function all(): array
    {
        return $this->repos->writer()->allByAssId($this->ass_id);
    }

    public function save(Writer $writer): void
    {
        $this->checkScope($writer);
        $this->repos->writer()->save($writer);
    }

    private function checkScope(Writer $writer)
    {
        if ($writer->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}