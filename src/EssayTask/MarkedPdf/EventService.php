<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\MarkedPdf;

use Edutiek\AssessmentService\EssayTask\Data\MarkedPdf;

interface EventService
{
    public function deleteByTaskId(int $task_id): void;
    public function deleteByWriterId(int $writer_id): void;
    public function deleteByCorrectorId(int $corrector_id): void;

}
