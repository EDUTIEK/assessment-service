<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class ExportFile implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;

    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;

    abstract public function getFileId(): string;
    abstract public function setFileId(string $file_id): self;

    abstract public function getType(): ExportType;
    abstract public function setType(ExportType $type): self;
}
