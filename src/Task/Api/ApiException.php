<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\Api;

use Exception;

class ApiException extends Exception
{
    /**
     * The id of requested or saved data is out of scope of this api
     * e.g. an ass_id that is not handled by the service
     */
    public const ID_SCOPE = 0;

    /**
     * The operation os not allowed due to the correction status
     */
    public const CORRECTION_STATUS = 1;
}