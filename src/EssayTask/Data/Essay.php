<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;

abstract class Essay implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getUuid(): string;
    public abstract function setUuid(string $uuid): self;
    public abstract function getWriterId(): int;
    public abstract function setWriterId(int $writer_id): self;
    public abstract function getWrittenText(): ?string;
    public abstract function setWrittenText(?string $written_text): self;
    public abstract function getRawTextHash(): string;
    public abstract function setRawTextHash(string $raw_text_hash): self;
    public abstract function getPdfVersion(): ?string;
    public abstract function setPdfVersion(?string $pdf_version): self;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): self;
    public abstract function getLastChange(): ?DateTimeImmutable;
    public abstract function setLastChange(?DateTimeImmutable $last_change): self;
    public abstract function getServiceVersion(): int;
    public abstract function setServiceVersion(int $service_version): self;
    public abstract function getFirstChange(): ?DateTimeImmutable;
    public abstract function setFirstChange(?DateTimeImmutable $first_change): self;
}
