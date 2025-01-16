<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Alert implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getTitle(): ?string;
    public abstract function setTitle(?string $title): void;
    public abstract function getMessage(): string;
    public abstract function setMessage(string $message): void;
    public abstract function getWriterId(): ?int;
    public abstract function setWriterId(?int $writer_id): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
    public abstract function getShownFrom(): ?DateTimeImmutable;
    public abstract function setShownFrom(?DateTimeImmutable $shown_from): void;
    public abstract function getShownUntil(): ?DateTimeImmutable;
    public abstract function setShownUntil(?DateTimeImmutable $shown_until): void;
}
