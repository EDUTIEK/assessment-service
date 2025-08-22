<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

class Factory
{
    private array $instances = [];

    public function __construct(private readonly Dependencies $dependencies)
    {
    }

    /**
     * Get the API for client systems
     * @param int $ass_id  id of the assessment
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
     * Get the API for the assessment component
     */
    public function forAssessment(): ForAssessment
    {
        return $this->instances[ForAssessment::class] ??= new ForAssessment(
            $this->dependencies,
            $this->internal()
        );
    }

    /**
     * Get the API for the assessment component
     */
    public function forTypes(int $ass_id, int $user_id): ForTypes
    {
        return $this->instances[ForTypes::class] ??= new ForTypes(
            $ass_id,
            $user_id,
            $this->internal(),
            $this->dependencies
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
