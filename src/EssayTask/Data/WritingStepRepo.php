<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface WritingStepRepo
{
    public function new(): WritingStep;
    public function one(int $id): ?WritingStep;
    public function hasByEssayIdAndHashAfter(int $essay_id, string $hash_after): bool;
    /** @return WritingStep[] */
    public function allByEssayId(int $essay_id): array;
    public function create(WritingStep $entity): void;
    public function deleteByEssayId(int $essay_id): void;
}
