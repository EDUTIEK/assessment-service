<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Api\Dependencies;
use Edutiek\AssessmentService\Assessment\Api\ForClients;
use Edutiek\AssessmentService\Assessment\Api\ForRest;

class Factory
{
    private array $instances = [];

    public function __construct(private readonly Dependencies $dependencies)
    {
    }

    /**
     * Get the API for client systems
     * @param int $ass_id  id of the assessment object
     * @param int $context_id  id of the permission context in which the object is used
     * @param int $user_id id of the currently active user
     */
    public function forClients(int $ass_id, int $user_id): ForClients
    {
        return $this->instances[ForClients::class][$ass_id][$user_id] ??= new ForClients(
            $ass_id,
            $user_id,
            $this->dependencies,
            $this->internal()
        );
    }

    /**
     * Get the API for REST calls
     */
    public function forRest(): ForRest
    {
        return $this->instances[ForRest::class] ??= new ForRest(
            $this->dependencies,
            $this->internal()
        );
    }

    /**
     * Get the factory for internal services
     */
    private function internal(): Internal
    {
        return $this->instances[Internal::class] ??= new Internal(
            $this->dependencies
        );
    }
}
