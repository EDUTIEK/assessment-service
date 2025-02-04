<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface CorrectorTaskPrefsRepo
{
    public function new(): CorrectorTaskPrefs;
    public function oneByCorrectorIdAndTaskId(int $corrector_id, int $task_id): ?CorrectorTaskPrefs;
    public function save(CorrectorTaskPrefs $entity): void;
    public function deleteByCorrectorIdAndTaskId(int $corrector_id, int $task_id): void;
}