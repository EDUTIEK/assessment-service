<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorComment;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\Task\Api\ApiException;

readonly class Service implements FullService
{
    public function __construct(
        private int $task_id,
        private int $writer_id,
        private Repositories $repos
    ) {
    }

    public function allByCorrectorId(int $corrector_id): array
    {
        return $this->repos->correctorComment()->allByTaskIdAndWriterIdAndCorrectorId($this->task_id, $this->writer_id, $corrector_id);
    }

    public function new(): CorrectorComment
    {
        return $this->repos->correctorComment()->new()->setTaskId($this->task_id)->setWriterId($this->writer_id);
    }

    public function save(CorrectorComment $comment): void
    {
        $this->checkScope($comment);
        $this->repos->correctorComment()->save($comment);
    }

    public function delete(): void
    {
        $this->repos->correctorComment()->deleteByTaskIdAndWriterId($this->task_id, $this->writer_id);
        #$this->repos->correctorPoints()->deleteByTaskIdAndWriterId($id); TODO
    }

    private function checkScope(CorrectorComment $comment)
    {
        if ($comment->getTaskId() !== $this->task_id && $comment->getWriterId() !== $this->writer_id) {
            throw new ApiException("wrong task_id and writer_id", ApiException::ID_SCOPE);
        }
    }
}
