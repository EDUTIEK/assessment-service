<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface LogEntryRepo
{
    public function new(): LogEntry;
    public function one(int $id): ?LogEntry;
    public function allByAssId(int $ass_id): array;
    public function create(LogEntry $alert): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
}
