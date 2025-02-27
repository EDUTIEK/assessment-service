<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

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
    public function forClients(int $ass_id): ForClients
    {
        return $this->instances[ForClients::class][$ass_id] ??= new ForClients(
            $ass_id,
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
