<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract readonly class Permissions implements AssessmentEntity
{
    abstract public function getAssId(): int;
    abstract public function getContextId(): int;
    abstract public function getUserId(): int;


    /**
     * Assessment is visible in lists
     */
    abstract public function getVisible(): bool;

    /**
     * Assessment can be clicked and functions are available depending on further permissions
     */
    abstract public function getRead(): bool;

    /**
     * Maintenance to organisational and technical settings, but notthe assessment content
     */
    abstract public function getMaintainSettings(): bool;

    /**
     * Maintenance of the assessments content (instructions, solution, resources)
     */
    abstract public function getMaintainContent(): bool;

    /**
     * Writing of the assessment can be maintained
     * - list of writers
     * - individual time settings
     * - remove authorizations
     * - add log entries and send messages
     */
    abstract public function getMaintainWriting(): bool;

    /**
     * Correction of the exam can be maintained
     * - list of correctors
     * - list of written assessments
     * - assignment of correctors
     * - remove authorizations
     * - stitch decision
     * - export results and documentation
     */
    abstract public function getMaintainCorrection(): bool;
}
