<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Permissions;

interface ReadService
{
    /**
     *
     */
    public function canEditMaterial() : bool;
}