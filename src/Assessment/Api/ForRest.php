<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;


use Edutiek\AssessmentService\Assessment\Authentication\FullService as AuthenticationFullService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;


class ForRest
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    )
    {
    }


    /**
     * Internal authentication service for REST handlers
     */
    private function authentication(int $ass_id, int $context_id): AuthenticationFullService
    {
        return $this->instances[AuthenticationService::class][$ass_id][$context_id] ??= new AuthenticationService(
            $ass_id,
            $context_id,
            $this->dependencies->repositories()
        );
    }
}
