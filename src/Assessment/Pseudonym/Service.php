<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Pseudonym;

use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\User\Service as UserService;

class Service implements FullService
{
    public function __construct(
        private LanguageService $language_service,
        private UserService $user_service,
    ) {
    }

    public function buildForWriter(int $id, int $user_id): string
    {
        $user = $this->user_service->getUser($user_id);
        return $this->language_service->txt('writer_x', ['x' => (string) $id]);
    }
}
