<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Permissions;

interface ReadService
{
    /**
     * Check if the current user can edit the fixed rating criteria for all correctors
     */
    public function canEditFixedRatingCriteria() : bool;

    /**
     * Check if the current user can edit own rating
     */
    public function canEditOwnRatingCriteria() : bool;
}