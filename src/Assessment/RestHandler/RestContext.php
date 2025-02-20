<?php

namespace Edutiek\AssessmentService\Assessment\RestHandler;

use Psr\Http\Message\ServerRequestInterface as Request;

interface RestContext
{
    /**
     * Get the route of the REST call
     * This is a path following the invoked skript
     * e.g. /writer/data
     */
    public function getRoute(): string;

    /**
     * Get the params of the REST call
     * These are added by the web apps as query params
     * They should be returned as a key/value array
     */
    public function getParams(): array;

    /**
     * Initialize the client system for a REST call
     */
    public function initCall(int $ass_id, int $context_id, int $user_id): void;

    /**
     * Extend a user session in the client system
     */
    public function setAlive(int $user_id);

    /**
     * Send a response to the front end application
     */
    public function sendResponse(int $status_code, string $body);
}
