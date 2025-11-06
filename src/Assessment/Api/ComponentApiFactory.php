<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

interface ComponentApiFactory
{
    /**
     * Get the names of components needed in the assessment
     * these are "Assessment", "Task" and all task types used by the assessment
     * @return string[]
     */
    public function components(int $ass_id, int $user_id): array;


    /**
     * Get the api of a named component
     * the name may be given in lower case, e.g. in REST paths
     */
    public function api(string $component): ?ComponentApi;
}
