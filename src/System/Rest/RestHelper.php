<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Rest;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface RestHelper
{
    /**
     * Generate a new authentication token
     */
    public function createToken(int $user_id): string;

    /**
     * Delete the current authentication token
     */
    public function deleteToken(): void;

    /**
     * Init the current REST call
     */
    public function init(): void;

    /**
     * Authenticate the request by token
     * @return int user id
     * @throws RestException
     */
    public function authenticate(): int;

    /**
     * Get the route of the current REST call
     * This is a path following the invoked skript
     * e.g. /writer/data
     */
    public function getRoute(): string;

    /**
     * Get the params of the current REST call
     * These are added by the web apps as query params
     * They should be returned as a key/value array
     */
    public function getParams(): array;

    /**
     * Parses the JSON body from the incoming HTTP request if the `Content-Type`
     * header indicates a JSON payload. Converts the body into an associative array
     */
    public function getJsonData(Request $request): array;

    /**
     * Prepare a full response with status code and JSON data
     * @param string|array $data
     */
    public function setJsonResponse(Response $response, int $status, $data = []): Response;

    /**
     * Send a plain response with data from the parameters
     */
    public function sendPlainResponse(int $status_code, string $body, array $headers = []): never;
}
