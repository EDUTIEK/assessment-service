<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

interface ObjectRepo
{
    /**
     * Get the core object permissions of a user in an assessment, depending on a context
     * Other permissions ae derived from these and from the status of the assessment
     */
    public function getObjectPermissions(int $ass_id, int $context_id, int $user_id): ObjectPermissions;

    /**
     * Get the object properties (title, description) of an assessment
     */
    public function getObjectProperties(int $ass_id): ObjectProperties;

    /**
     * Save the object properties (title, description) of an assessment
     */
    public function saveObjectProperties(ObjectProperties $properties): void;
}