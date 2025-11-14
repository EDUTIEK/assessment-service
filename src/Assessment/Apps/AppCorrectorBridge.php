<?php

namespace Edutiek\AssessmentService\Assessment\Apps;

interface AppCorrectorBridge extends AppBridge
{
    public function getItem(int $assignment_id): ?array;
}
