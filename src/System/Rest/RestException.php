<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Rest;

use Exception;

class RestException extends Exception
{
    /**
     * Should be thrown when the user is not found or can't be authorized
     */
    public const int UNAUTHORIZED = 401;

    /**
     * Should be thrown when the user does not have the permission for the request
     */
    public const int FORBIDDEN = 403;

    /**
     * Should be thrown when the assessment or context does not exist
     */
    public const int NOT_FOUND = 404;

    /**
     * Should be thrown for any unspecific error or exception
     */
    public const int INTERNAL_SERVER_ERROR = 500;

    /**
     * Should be thrown when the REST route is not implemented
     */
    public const int NOT_IMPLEMENTED = 501;

    /**
     * Can be thrown to simulate network errors, e.g., to test the web apps
     */
    public const int SERVICE_UNAVAILABLE = 503;
}
