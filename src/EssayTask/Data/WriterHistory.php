<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Data;

use DateTimeImmutable;
use Edutiek\AssessmentService\System\Api\HasHtml;

abstract class WriterHistory implements EssayTaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getEssayId(): int;
    abstract public function setEssayId(int $essay_id): self;
    abstract public function getTimestamp(): ?DateTimeImmutable;
    abstract public function setTimestamp(?DateTimeImmutable $timestamp): self;
    #[HasHtml]
    abstract public function getContent(): ?string;
    abstract public function setContent(?string $content): self;
    abstract public function getIsDelta(): int;
    abstract public function setIsDelta(int $is_delta): self;
    abstract public function getHashBefore(): ?string;
    abstract public function setHashBefore(?string $hash_before): self;
    abstract public function getHashAfter(): ?string;
    abstract public function setHashAfter(?string $hash_after): self;
}
