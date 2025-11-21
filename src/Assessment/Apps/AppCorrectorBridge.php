<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

interface AppCorrectorBridge extends AppBridge
{
    public function getItem(int $task_id, int $writer_id): ?array;
}
