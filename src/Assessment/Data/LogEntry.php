<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use Edutiek\AssessmentService\Assessment\LogEntry\Category as LogEntryCategory;
use DateTimeImmutable;

abstract class LogEntry implements AssessmentEntity
{
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;

    abstract public function getId(): int;
    abstract public function setId(int $id): self;

    abstract public function getTimestamp(): ?DateTimeImmutable;
    abstract public function setTimestamp(?DateTimeImmutable $timestamp): self;

    abstract public function getCategory(): LogEntryCategory;
    abstract public function setCategory(LogEntryCategory $category): self;

    abstract public function getEntry(): ?string;
    abstract public function setEntry(?string $entry): self;
}
