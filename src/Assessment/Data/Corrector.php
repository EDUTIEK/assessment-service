<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class Corrector implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getUserId(): int;
    public abstract function setUserId(int $user_id): void;
    public abstract function getCorrectionReport(): ?string;
    public abstract function setCorrectionReport(?string $correction_report): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
}
