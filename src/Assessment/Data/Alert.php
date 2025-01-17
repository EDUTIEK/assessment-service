<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Alert implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getTitle(): ?string;
    public abstract function setTitle(?string $title): self;
    public abstract function getMessage(): string;
    public abstract function setMessage(string $message): self;
    public abstract function getWriterId(): ?int;
    public abstract function setWriterId(?int $writer_id): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
    public abstract function getShownFrom(): ?DateTimeImmutable;
    public abstract function setShownFrom(?DateTimeImmutable $shown_from): self;
    public abstract function getShownUntil(): ?DateTimeImmutable;
    public abstract function setShownUntil(?DateTimeImmutable $shown_until): self;
}
