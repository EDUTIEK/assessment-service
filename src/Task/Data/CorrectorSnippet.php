<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

abstract class CorrectorSnippet
{
    abstract public function getId(): int;
    abstract public function setId(int $id): void;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getKey(): string;
    abstract public function setKey(string $key): self;
    abstract public function getPurpose(): string;
    abstract public function setPurpose(string $purpose): self;
    abstract public function getShortcut(): ?string;
    abstract public function setShortcut(?string $shortcut): self;
    abstract public function getText(): ?string;
    abstract public function setText(?string $text): self;
}
