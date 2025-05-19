<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\WritingSettings;

use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;

interface FullService
{
    public function get(): WritingSettings;
    public function save(WritingSettings $settings): void;
}