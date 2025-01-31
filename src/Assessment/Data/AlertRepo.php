<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface AlertRepo
{
    public function new(): Alert;
    public function one(int $id): ?Alert;
    public function allByAssId(int $ass_id): array;
    public function create(Alert $alert): void;
    public function delete(int $id): void;
    public function deleteByAssId(int $ass_id): void;
}
