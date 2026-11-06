<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Rest;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * This service must be provided by the client system for the REST service
 */
interface RestContext
{
    /**
     * Get the headers of the current REST call
     * @return array<string, string>
     */
    public function getHeaders(): array;

    /**
     * Get the request method of the current REST call
     * @return string
     */
    public function getRequestMethod(): string;

    /**
     * Get the route of the current REST call
     * This is a path following the invoked skript
     * e.g., /writer/data
     */
    public function getRoute(): string;

    /**
     * Get the params of the current REST call
     * These are added by the web apps as query params
     * They should be returned as a key/value array
     */
    public function getParams(): array;

    /**
     * Initialize the client system for a REST call
     */
    public function initCall(): void;

    /**
     * Init a user account for the REST call
     */
    public function initUser(int $user_id): void;

    /**
     * Extend a user session in the client system
     */
    public function setAlive(int $user_id): void;

    /**
     * Send a response to the front end application
     */
    public function sendResponse(int $status_code, string $body, array $headers = []): never;
}
