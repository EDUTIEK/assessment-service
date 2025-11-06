<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

interface ComponentApiFactory
{
    /**
     * Get the names of components needed in the assessment
     * these are "Assessment", "Task" and all usesd task types
     * @return string[]
     */
    public function components(int $ass_id, int $user_id): array;


    /**
     * Get the api for a named component
     * Component may be given in lower case, e.g. in REST paths
     */
    public function api(string $component): ?ComponentApi;
}
