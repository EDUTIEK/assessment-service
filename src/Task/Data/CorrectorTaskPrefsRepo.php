<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface CorrectorTaskPrefsRepo
{
    public function new(): CorrectorTaskPrefs;
    public function oneByCorrectorIdAndTaskId(int $corrector_id, int $task_id): ?CorrectorTaskPrefs;

    /**
     * @param int $task_id
     * @return CorrectorTaskPrefs[]
     */
    public function allByTaskId(int $task_id): array;
    public function save(CorrectorTaskPrefs $entity): void;
    public function deleteByCorrectorIdAndTaskId(int $corrector_id, int $task_id): void;
    public function deleteByTaskId(int $task_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;
}
