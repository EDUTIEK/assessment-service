<?php

namespace Edutiek\AssessmentService\Assessment\Pseudonym;

interface FullService
{
    public function buildForWriter(int $id, int $user_id): string;
}