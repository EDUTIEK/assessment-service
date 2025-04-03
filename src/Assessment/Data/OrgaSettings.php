<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;
use ReflectionClass;

abstract class OrgaSettings implements AssessmentEntity
{
    use \Edutiek\AssessmentService\System\Api\Entity;

    /** @var OrgaSettingsError[] */
    private $validation_errors = [];

    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;

    /**
     * Online switch of the assessment
     * This controls the read access for persons who don't have maintenance permissions
     */
    abstract public function getOnline(): bool;
    abstract public function setOnline(bool $online): self;

    abstract public function getParticipationType(): ParticipationType;
    abstract public function setParticipationType(ParticipationType $participation_type): self;

    abstract public function getMultiTasks(): bool;
    abstract public function setMultiTasks(bool $multi): self;

    /**
     * Organisational description that is shown on the starting page of participants
     * Rich Text with HTML
     */
    abstract public function getDescription(): ?string;
    abstract public function setDescription(?string $description): self;

    abstract public function getClosingMessage(): ?string;
    abstract public function setClosingMessage(?string $closing_message): self;

    abstract public function getWritingStart(): ?DateTimeImmutable;
    abstract public function setWritingStart(?DateTimeImmutable $writing_start): self;

    abstract public function getWritingEnd(): ?DateTimeImmutable;
    abstract public function setWritingEnd(?DateTimeImmutable $writing_end): self;

    abstract public function getWritingLimitMinutes(): ?int;
    abstract public function setWritingLimitMinutes(?int $writing_limit_minutes): self;

    abstract public function getCorrectionStart(): ?DateTimeImmutable;
    abstract public function setCorrectionStart(?DateTimeImmutable $correction_start): self;

    abstract public function getCorrectionEnd(): ?DateTimeImmutable;
    abstract public function setCorrectionEnd(?DateTimeImmutable $correction_end): self;

    abstract public function getReviewStart(): ?DateTimeImmutable;
    abstract public function setReviewStart(?DateTimeImmutable $review_start): self;

    abstract public function getReviewEnd(): ?DateTimeImmutable;
    abstract public function setReviewEnd(?DateTimeImmutable $review_end): self;

    abstract public function getKeepAvailable(): bool;
    abstract public function setKeepAvailable(bool $keep_available): self;

    abstract public function getSolutionAvailableDate(): ?DateTimeImmutable;
    abstract public function setSolutionAvailableDate(?DateTimeImmutable $solution_available_date): self;

    abstract public function getResultAvailableType(): ResultAvailableType;
    abstract public function setResultAvailableType(ResultAvailableType $result_available_type): self;

    abstract public function getResultAvailableDate(): ?DateTimeImmutable;
    abstract public function setResultAvailableDate(?DateTimeImmutable $result_available_date): self;

    abstract public function getSolutionAvailable(): bool;
    abstract public function setSolutionAvailable(bool $solution_available): self;

    abstract public function getReviewEnabled(): bool;
    abstract public function setReviewEnabled(bool $review_enabled): self;

    abstract public function getReviewNotification(): bool;
    abstract public function setReviewNotification(bool $review_notification): self;

    abstract public function getReviewNotifText(): ?string;
    abstract public function setReviewNotifText(?string $review_notif_text): self;

    abstract public function getStatisticsAvailable(): bool;
    abstract public function setStatisticsAvailable(bool $statistics_available): self;

    public function addValidationError(OrgaSettingsError $error)
    {
        $this->validation_errors[] = $error;
    }

    public function getValidationErrors(): array
    {
        return $this->validation_errors;
    }
}
