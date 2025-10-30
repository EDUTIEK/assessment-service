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
    public function forClients(int $ass_id, int $user_id): ForClients
    {
        return $this->instances[ForClients::class][$ass_id][$user_id] ??= new ForClients(
            $ass_id,
            $user_id,
            $this->internal(),
        );
    }

    /**
     * Get the API for constraint handling
     */
    public function forConstraints(): ForConstraints
    {
        return $this->instances[ForConstraints::class] ??= new ForConstraints($this->internal());
    }

    /**
     * Get the API for event handling
     */
    public function forEvents(): ForEvents
    {
        return $this->instances[ForEvents::class] ??= new ForEvents($this->internal());
    }

    /**
     * Get the API for the task and assessment
     */
    public function forServices(): ForServices
    {
        return $this->instances[ForServices::class] ??= new ForServices($this->internal());
    }

    /**
     * Get the factory for internal services
     */
    private function internal(): Internal
    {
        return $this->instances[Internal::class] ??= new Internal($this->dependencies);
    }
}
