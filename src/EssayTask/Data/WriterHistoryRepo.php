<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface WriterHistoryRepo
{
    public function new(): WriterHistory;
    public function one(int $id): ?WriterHistory;
    public function hasByEssayIdAndHashAfter(int $essay_id, string $hash_after): bool;
    /** @return WriterHistory[] */
    public function allByEssayId(int $essay_id): array;
    public function create(WriterHistory $entity): void;
    public function deleteByEssayId(int $essay_id): void;
}