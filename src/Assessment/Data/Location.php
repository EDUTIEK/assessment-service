<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class Location implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTitle(): string;
    abstract public function setTitle(string $title): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
}
