<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface CorrectorTemplateRepo
{
    public function one(int $id): ?CorrectorTemplate;
    public function new(int $task_id, int $corrector_id): CorrectorTemplate;
    public function oneByTaskIdAndCorrectorId(int $task_id, int $corrector_id): ?CorrectorTemplate;
    public function allByCorrectorId(int $corrector_id): array;
    public function allByTaskId(int $task_id): array;
    public function save(CorrectorTemplate $entity): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
}
