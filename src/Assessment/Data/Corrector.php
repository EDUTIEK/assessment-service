<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class Corrector implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): self;
    public abstract function getUserId(): int;
    public abstract function setUserId(int $user_id): self;
    public abstract function getCorrectionReport(): ?string;
    public abstract function setCorrectionReport(?string $correction_report): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
}
