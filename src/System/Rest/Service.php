<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Rest;

use Edutiek\AssessmentService\System\Data\TokenRepo;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Edutiek\AssessmentService\System\Data\Token;
use Fig\Http\Message\StatusCodeInterface;

class Service implements RestHelper
{
    private ?Token $token = null;

    public function __construct(
        private readonly TokenRepo $repo,
        private readonly ConfigReadService $config,
        private readonly UserReadService $users,
        private readonly RestContext $context
    ) {
    }

    public function createToken(int $user_id): Token
    {
        $this->token = $this->repo->new($user_id);
        return $this->token;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function deleteToken(): void
    {
        if ($this->token !== null) {
            $this->repo->delete($this->token->getId());
        }
        $this->token = null;
    }

    public function init(): void
    {
        $this->context->initCall();

        if ($this->context->getRequestMethod() === 'OPTIONS') {
            $this->context->sendResponse(StatusCodeInterface::STATUS_OK, '', $this->getCommonHeaders());
        }
    }

    public function authenticate(): int
    {
        $auth = $this->context->getHeaders()['Authorization'] ?? [];

        if (count($auth) == 1 && preg_match('/Bearer\s(\S+)/', reset($auth), $matches)) {
            $this->token = $this->repo->oneByToken($matches[1]);
        } else {
            throw new RestException('invalid authentication header', RestException::UNAUTHORIZED);
        }

        if ($this->token === null) {
            throw new RestException('token is not found', RestException::UNAUTHORIZED);
        }
        if (!$this->token->isValid()) {
            throw new RestException('token is expired', RestException::UNAUTHORIZED);
        }
        if ($this->config->getConfig()->getSimulateOffline()) {
            throw new RestException('offline mode', RestException::SERVICE_UNAVAILABLE);
        }
        if ($this->users->getLoginByUserId($this->token->getUserId()) == '') {
            throw new RestException('user not found', RestException::NOT_FOUND);
        }

        $this->repo->save($this->token->setNewValidity());
        $this->context->initUser($this->token->getUserId());
        $this->context->setAlive($this->token->getUserId());

        return $this->token->getUserId();
    }

    public function getRoute(): string
    {
        return $this->context->getRoute();
    }

    public function getParams(): array
    {
        return $this->context->getParams();
    }

    public function getJsonData(Request $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (strstr($contentType, 'application/json')) {
            $contents = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return (array) $contents;
            }
        }
        return [];
    }

    public function setJsonResponse(Response $response, int $status, $data = []): Response
    {
        foreach ($this->getCommonHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $response = $response
            ->withHeader('X-Server-Time', (string) time())
            ->withStatus($status);

        if ($this->token !== null) {
            $response = $response->withHeader('X-Access-Token', $this->token->getToken());
        }

        $response->getBody()->write(json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE));
        return $response;
    }

    public function sendPlainResponse(int $status_code, string $body): never
    {
        $this->context->sendResponse($status_code, $body, $this->getCommonHeaders());
    }

    private function getCommonHeaders(): array
    {
        // todo: distinct 'Access-Control-Allow-Origin' between local development and production
        return [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Expose-Headers' => 'X-Access-Token, X-Server-Time',
        ];
    }
}
