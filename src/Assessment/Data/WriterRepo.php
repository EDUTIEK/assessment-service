<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface WriterRepo
{
    public function new(): Writer;
    public function one(int $id): ?Writer;
    public function hasByWriterIdAndAssId(int $writer_id, int $ass_id): bool;
    public function oneByUserIdAndAssId(int $user_id, int $ass_id): ?Writer;
    /** @return Writer[] */
    public function allByUserIdsAndAssId(array $user_ids, int $ass_id): array;
    /** @return Writer[] */
    public function allByWriterIdsAndAssId(array $writer_ids, int $ass_id): array;
    /** @return Writer[] */
    public function allByAssId(int $ass_id): array;
    public function save(Writer $entity): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
}
