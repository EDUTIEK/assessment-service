<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class Location implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getTitle(): string;
    public abstract function setTitle(string $title): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
}
