<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Corrector;

use Edutiek\AssessmentService\Assessment\Data\Corrector;

interface ReadService
{
    public function has(int $corrector_id): bool;
    public function oneByUserId(int $user_id): ?Corrector;
    /** @return Corrector[] */
    public function all(): array;
    /** @return Corrector[] */
    public function some(array $corrector_ids): array;
    public function oneById(int $corrector_id): ?Corrector;
}
