<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\Comments\Service as CommentsService;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service as HtmlService;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

class Internal
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    public function htmlProcessing(): HtmlService
    {
        return $this->instances[HtmlService::class] ??= new HtmlService(
            $this->comments()
        );
    }

    public function language(int $user_id): LanguageService
    {
        return $this->instances[LanguageService::class][$user_id] ??=
            $this->dependencies->systemApi()->loadLanguagFromFile($user_id, __DIR__ . '/../Languages/');
    }

    public function comments(): CommentsService
    {
        return $this->instances[CommentsService::class] ??= new CommentsService(
            $this->dependencies->systemApi()->imageSketch()
        );
    }
}
