<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class ExportSettings implements AssessmentEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;

    abstract public function getResultExportFormat(): ResultExportFormat;
    abstract public function setResultExportFormat(ResultExportFormat $format): self;
}
