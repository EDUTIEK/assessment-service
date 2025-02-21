<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Exception;

class RestException extends Exception
{
    /**
     * Should be thrown when the user is not found or can't be authorized
     */
    public const UNAUTHORIZED = 401;

    /**
     * Should be thrown when the user does not have the permission for the request
     */
    public const FORBIDDEN = 403;

    /**
     * Should be thrown when the assessment or context does not exist
     */
    public const NOT_FOUND = 404;

    /**
     * Should be thrown for any unspecific error or exception
     */
    public const INTERNAL_SERVER_ERROR = 500;

    /**
     * Should be thrown when the REST route is not implemented
     */
    public const NOT_IMPLEMENTED = 501;

    /**
     * Can be thrown to simulate network errors, e.g. to test the web apps
     */
    public const SERVICE_UNAVAILABLE = 503;
}
