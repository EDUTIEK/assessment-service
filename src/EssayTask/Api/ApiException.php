<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Exception;

class ApiException extends Exception
{
    /**
     * The id of requested or saved data is out of scope of this api
     * e.g. an ass_id that is not handled by the service
     */
    public const ID_SCOPE = 0;

    /**
     * The operation is not allowed due to the writing status of an essay
     */
    public const WRITING_STATUS = 1;

    /**
     * The operation is not allowed due to the correction status of an essay
     */
    public const CORRECTION_STATUS = 2;
}