<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

readonly abstract class ContextInfo implements AssessmentEntity
{
    /**
     * Context id of the assessment in the client system
     */
    abstract public function getContextId(): int;

    /**
     * Title of the parent object (single line)
     */
    abstract public function getParentTitle(): string;

    /**
     * Short description of the parent object (multi-line)
     */
    abstract public function getParentDescription(): string;
}
