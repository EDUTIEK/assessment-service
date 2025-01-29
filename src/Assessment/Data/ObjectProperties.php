<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

abstract class ObjectProperties implements AssessmentEntity
{
    /**
     * Id of the assessment object in the client system, can only be read
     * Objects have to be created with client system functions
     */
    abstract public function getAssessmentId(): int;

    /**
     * Title of the assessment (single line)
     */
    abstract public function getTitle(): string;
    abstract public function setTitle(string $title): ObjectProperties;

    /**
     * Short description of the assessment (multi-line) for showing in lists
     */
    abstract public function getDescription(): string;
    abstract public function setDescription(string $description): ObjectProperties;
}
