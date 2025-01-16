<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;

abstract class Essay implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getUuid(): string;
    public abstract function setUuid(string $uuid): void;
    public abstract function getWriterId(): int;
    public abstract function setWriterId(int $writer_id): void;
    public abstract function getWrittenText(): ?string;
    public abstract function setWrittenText(?string $written_text): void;
    public abstract function getRawTextHash(): string;
    public abstract function setRawTextHash(string $raw_text_hash): void;
    public abstract function getPdfVersion(): ?string;
    public abstract function setPdfVersion(?string $pdf_version): void;
    public abstract function getTaskId(): int;
    public abstract function setTaskId(int $task_id): void;
    public abstract function getLastChange(): ?DateTimeImmutable;
    public abstract function setLastChange(?DateTimeImmutable $last_change): void;
    public abstract function getServiceVersion(): int;
    public abstract function setServiceVersion(int $service_version): void;
    public abstract function getFirstChange(): ?DateTimeImmutable;
    public abstract function setFirstChange(?DateTimeImmutable $first_change): void;
}
