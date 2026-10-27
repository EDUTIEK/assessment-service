<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Views\Api;

class Factory
{
    private array $instances = [];

    public function __construct(private readonly Dependencies $dependencies)
    {
    }

    /**
     * Get the API for REST access
     */
    public function forClients(): ForClients
    {
        return $this->instances[ForClients::class] ??= new ForClients($this->dependencies);
    }

    /**
     * Get the API for REST access
     */
    public function forRest(): ForRest
    {
        return $this->instances[ForRest::class] ??= new ForRest($this->dependencies);
    }
}
