<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class PdfConfig implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getPurpose(): string;
    abstract public function setPurpose(string $purpose): self;
    abstract public function getComponent(): string;
    abstract public function setComponent(string $component): self;
    abstract public function getKey(): string;
    abstract public function setKey(string $key): self;
    abstract public function getIsActive(): bool;
    abstract public function setIsActive(bool $active): self;
    abstract public function getPosition(): int;
    abstract public function setPosition(int $position): self;
}
