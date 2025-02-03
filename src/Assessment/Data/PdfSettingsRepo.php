<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface PdfSettingsRepo
{
    public function new(): PdfSettings;
    public function one(int $ass_id): ?PdfSettings;
    public function save(PdfSettings $entity): void;
    public function delete(int $ass_id): void;
}
