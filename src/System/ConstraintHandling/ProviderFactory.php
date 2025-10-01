<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling;

/**
 * Factory for constraint check providers
 */
interface ProviderFactory
{
    public function provider(int $ass_id, int $user_id): Provider;
}
