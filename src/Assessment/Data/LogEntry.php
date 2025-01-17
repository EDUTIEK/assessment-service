<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class LogEntry implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getTimestamp(): ?DateTimeImmutable;
    public abstract function setTimestamp(?DateTimeImmutable $timestamp): self;
    public abstract function getCategory(): string;
    public abstract function setCategory(string $category): self;
    public abstract function getEntry(): ?string;
    public abstract function setEntry(?string $entry): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
}
