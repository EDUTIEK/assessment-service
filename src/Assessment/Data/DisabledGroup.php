<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class DisabledGroup implements AssessmentEntity
{
    abstract public function setAssId(int $ass_id): self;
    abstract public function getAssId(): int;
    abstract public function setName(string $name): self;
    abstract public function getName(): string;
}
