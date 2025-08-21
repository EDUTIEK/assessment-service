<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\CorrectorComment;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\CorrectorComment;
use Edutiek\AssessmentService\EssayTask\Api\ApiException;

readonly class Service implements FullService
{
    public function __construct(
        private int $essay_id,
        private Repositories $repos
    ) {
    }

    public function allByCorrectorId(int $corrector_id): array
    {
        return $this->repos->correctorComment()->allByEssayIdAndCorrectorId($this->essay_id, $corrector_id);
    }

    public function new(): CorrectorComment
    {
        return $this->repos->correctorComment()->new()->setEssayId($this->essay_id);
    }

    public function save(CorrectorComment $comment): void
    {
        $this->checkScope($comment);
        $this->repos->correctorComment()->save($comment);
    }

    public function deleteByEssayId(int $id): void
    {
        $this->repos->correctorComment()->deleteByEssayId($id);
        $this->repos->correctorPoints()->deleteByEssayId($id);
    }

    private function checkScope(CorrectorComment $comment)
    {
        if ($comment->getEssayId() !== $this->essay_id) {
            throw new ApiException("wrong essay_id", ApiException::ID_SCOPE);
        }
    }
}
