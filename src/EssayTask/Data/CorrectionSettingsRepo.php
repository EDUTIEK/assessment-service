<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

interface CorrectionSettingsRepo
{
    public function new(): CorrectionSettings;
    public function one(int $ass_id): ?CorrectionSettings;
    public function save(CorrectionSettings $entity): void;
    public function delete(int $ass_id): void;
}