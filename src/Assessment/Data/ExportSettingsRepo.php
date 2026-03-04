<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface ExportSettingsRepo
{
    public function new(): ExportSettings;
    public function one(int $ass_id): ?ExportSettings;
    public function save(ExportSettings $entity): void;
    public function delete(int $ass_id): void;
}
