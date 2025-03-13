<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\WritingSettings\Service as WritingSettingsService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService as WritingSettingsFullService;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private int $user_id,
        private readonly Dependencies $dependencies
    ) {
    }

    public function writingSettings(): WritingSettingsFullService
    {
        return $this->instances[WritingSettingsService::class] = new WritingSettingsService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }
}
