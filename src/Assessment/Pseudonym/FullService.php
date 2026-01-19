<?php

namespace Edutiek\AssessmentService\Assessment\Pseudonym;

use Edutiek\AssessmentService\Assessment\Data\Pseudonymization;

interface FullService
{
    public function options(): array;
    public function changeForAll(Pseudonymization $pseudonymisation): void;
    public function buildForWriter(int $id, int $user_id): string;
}
