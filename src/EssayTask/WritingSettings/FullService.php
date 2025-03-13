<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\WritingSettings;

use ILIAS\Plugin\LongEssayAssessment\EssayTask\Data\WritingSettings;

interface FullService
{
    public function get(): WritingSettings;
    public function validate(WritingSettings $settings): bool;
    public function save(WritingSettings $settings): void;
}