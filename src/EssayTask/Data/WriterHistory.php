<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;

abstract class WriterHistory implements EssayTaskEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getEssayId(): int;
    public abstract function setEssayId(int $essay_id): self;
    public abstract function getTimestamp(): ?DateTimeImmutable;
    public abstract function setTimestamp(?DateTimeImmutable $timestamp): self;
    public abstract function getContent(): ?string;
    public abstract function setContent(?string $content): self;
    public abstract function getIsDelta(): int;
    public abstract function setIsDelta(int $is_delta): self;
    public abstract function getHashBefore(): ?string;
    public abstract function setHashBefore(?string $hash_before): self;
    public abstract function getHashAfter(): ?string;
    public abstract function setHashAfter(?string $hash_after): self;
}
