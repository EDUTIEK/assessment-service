<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Supervision;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

class Service implements FullService
{
    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly Repositories $repos,
        private readonly LanguageService $language,
    ) {
    }
}
