<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as Authentication;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as Permissions;
use Edutiek\AssessmentService\Assessment\Data\TokenPurpose;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\System\File\Delivery as FileDelivery;
use Edutiek\AssessmentService\System\File\Disposition;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

readonly class RestHelper
{
    public function __construct(
        private int $ass_id,
        private int $context_id,
        private int $user_id,
        private Authentication $auth,
        private Permissions $permissions,
        private Repositories $repos,
        private ConfigReadService $config_service,
        private UserReadService $user_service,
        private FileDelivery $file_delivery
    ) {
    }

    /**
     * Check the authentication of the rwquesr
     */
    public function checkAuth(TokenPurpose $purpose, string $signature): void
    {
        $token = $this->auth->getToken($this->user_id, $purpose);
        if ($token === null) {
            throw new RestException(RestException::UNAUTHORIZED, 'current token is not found');
        }
        if (!$this->auth->checkValidity($token)) {
            throw new RestException(RestException::UNAUTHORIZED, 'current token is expired');
        }
        if (!$this->auth->checkSignature($token, $signature)) {
            throw new RestException(RestException::UNAUTHORIZED, 'signature is wrong');
        }
    }

    /**
     * Check if the user has access to the assessment
     */
    public function checkAccess()
    {
        if ($this->config_service->getConfig()->getSimulateOffline()) {
            throw new RestException('offline mode', RestException::SERVICE_UNAVAILABLE);
        }

        if ($this->user_service->getUser($this->user_id) === null) {
            throw new RestException('user not found', RestException::NOT_FOUND);
        }
        if (!$this->repos->properties()->exists($this->ass_id, $this->context_id)) {
            throw new RestException('assessment not found', RestException::NOT_FOUND);
        }
        if (!$this->permissions->canDoRestCall()) {
            throw new RestException('REST call not allowed', RestException::FORBIDDEN);
        }
    }

    /**
     * Parses the JSON body from the incoming HTTP request if the `Content-Type`
     * header indicates a JSON payload. Converts the body into an associative
     * array and attaches it to the request.
     *
     * @param Request $request The HTTP request instance to process.
     */
    public function parseJsonBody(Request $request)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            }
        }
    }

    /**
     * Set a new expiration time for the data token and set it in the response
     */
    public function refreshDataToken(Response $response)
    {
        $token = $this->auth->getToken($this->user_id, TokenPurpose::DATA);
        $token->setValidUntil($this->auth->newValitity(TokenPurpose::DATA));
        $this->auth->saveToken($token);
        $response = $response->withHeader('LongEssayDataToken', $token->getToken());
    }

    /**
     * Generate a new data token and set it in the response
     */
    public function setNewDataToken(Response $response)
    {
        $token = $this->auth->getToken($this->user_id, TokenPurpose::DATA);
        $token->setValidUntil($this->auth->newValitity(TokenPurpose::DATA));
        $this->auth->saveToken($token);
        $response = $response->withHeader('LongEssayDataToken', $token->getToken());
    }

    /**
     * Generate a new file token and set it in the response
     */
    public function setNewFileToken(Response $response)
    {
        $token = $this->auth->getToken($this->user_id, TokenPurpose::FILE);
        $token->setValidUntil($this->auth->newValitity(TokenPurpose::FILE));
        $this->auth->saveToken($token);
        $response = $response->withHeader('LongEssayFileToken', $token->getToken());
    }

    /**
     * Modify the response with a status code and json return
     * @param string|array $json
     */
    public function setResponse(Response $response, int $status, $json = []): Response
    {
        $response = $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('LongEssayTime', (string) time())
            ->withStatus($status);
        $response->getBody()->write(json_encode($json));
        return $response;
    }

    /**
     * Send a resource file as inline
     */
    public function sendFile(string $file_id): Response
    {
        $this->file_delivery->sendFile($file_id, Disposition::INLINE);
    }
}
