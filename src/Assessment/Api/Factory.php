<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Api\Dependencies;
use Edutiek\AssessmentService\Assessment\Api\ForClients;
use Edutiek\AssessmentService\Assessment\Api\ForRest;

class Factory
{
    protected static array $instances = [];

    public function __construct(private readonly Dependencies $dependencies) {}

    /**
     * Get the API for client systems
     */
    public function forClients(int $ass_id, int $context_id) : ForClients
    {
        return self::$instances[ForClients::class][$ass_id][$context_id] ??= new ForClients(
            $ass_id, $context_id, $this->dependencies);
    }

    /**
     * Get the API for REST calls
     */
    public function forRest(int $ass_id, int $context_id) : ForRest
    {
        return self::$instances[ForRest::class] ??= new ForRest($this->dependencies);
    }
}