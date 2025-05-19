<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\TaskSettings;

use Edutiek\AssessmentService\EssayTask\Data\TaskSettings;

interface FullService
{
    public function get(): TaskSettings;
    public function save(TaskSettings $settings): void;
}