<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface WriterNoticeRepo
{
    public function new(): WriterNotice;
    public function oneByEssayIdAndNo(int $essay_id, int $note_no): ?WriterNotice;
    /** @return WriterNotice[] */
    public function allByEssayId(int $essay_id): array;
    public function save(WriterNotice $entity): void;
    public function delete(WriterNotice $entity): void;
    public function deleteByEssayId(int $essay_id): void;
}
