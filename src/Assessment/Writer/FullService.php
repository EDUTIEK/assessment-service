<?php

namespace Edutiek\AssessmentService\Assessment\Writer;

use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService extends ReadService
{
    /**
     * Get or create a writer of the assessment by its user id
     */
    public function getByUserId(int $user_id): Writer;
    public function save(Writer $writer): void;
    public function validate(Writer $writer): bool;

    public function authorizeWriting(Writer $writer, int $by_user_id, bool $as_admin): void;
    public function removeWritingAuthorization(Writer $writer, int $by_user_id): void;
    public function removeCorrectionFinalisation(Writer $writer, int $by_user_id): void;

    public function changeCorrectionStatus(Writer $writer, CorrectionStatus $status, int $by_user_id): void;

    public function changeWorkingTime(
        Writer $writer,
        ?\DateTimeImmutable $earliest_start,
        ?\DateTimeImmutable $latest_end,
        ?int $time_limit_minutes,
        int $by_user_id
    ): bool;
    public function removeWorkingTime(Writer $writer, int $by_user_id): void;
    public function remove(Writer $writer, ?int $by_user_id = null): void;
    public function exclude(Writer $writer, int $by_user_id): void;
    public function repealExclusion(Writer $writer, int $by_user_id): void;
}
