<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Essay;

use Edutiek\AssessmentService\EssayTask\Data\Essay;

interface FullService
{
    /** @return Essay[] */
    public function allByWriterId(int $writer_id): array;
    public function new(int $writer_id, int $task_id): Essay;
    public function save(Essay $essay);
}