<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class LogEntry implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getTimestamp(): ?DateTimeImmutable;
    public abstract function setTimestamp(?DateTimeImmutable $timestamp): void;
    public abstract function getCategory(): string;
    public abstract function setCategory(string $category): void;
    public abstract function getEntry(): ?string;
    public abstract function setEntry(?string $entry): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
}
