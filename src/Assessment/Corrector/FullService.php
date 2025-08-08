<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Corrector;

use Edutiek\AssessmentService\Assessment\Data\Corrector;

Interface FullService extends ReadService
{
    public function hasReports(): bool;
    public function remove(Corrector $corrector);

    public function getByUserId(int $user_id): Corrector;
}