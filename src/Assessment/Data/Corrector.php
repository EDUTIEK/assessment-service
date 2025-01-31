<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class Corrector implements AssessmentEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getUserId(): int;
    abstract public function setUserId(int $user_id): self;
    abstract public function getCorrectionReport(): ?string;
    abstract public function setCorrectionReport(?string $correction_report): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
}
