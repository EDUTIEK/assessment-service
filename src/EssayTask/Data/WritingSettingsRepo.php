<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface WritingSettingsRepo
{
    public function new(): WritingSettings;
    public function one(int $ass_id): ?WritingSettings;
    public function save(WritingSettings $entity): void;
    public function delete(int $ass_id): void;

}