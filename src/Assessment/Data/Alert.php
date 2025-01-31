<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Alert implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;

    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;

    abstract public function getTitle(): ?string;
    abstract public function setTitle(?string $title): self;

    abstract public function getMessage(): string;
    abstract public function setMessage(string $message): self;

    abstract public function getWriterId(): ?int;
    abstract public function setWriterId(?int $writer_id): self;

    abstract public function getShownFrom(): ?DateTimeImmutable;
    abstract public function setShownFrom(?DateTimeImmutable $shown_from): self;

    abstract public function getShownUntil(): ?DateTimeImmutable;
    abstract public function setShownUntil(?DateTimeImmutable $shown_until): self;
}
