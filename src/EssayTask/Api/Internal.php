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

    /**
     * Translation of language variables
     */
    public function language(string $code) : LanguageService
    {
        return $this->instances[LanguageService::class][$code] = $this->dependencies->systemApi()->language()
            ->addLanguage('de', require(__DIR__ . '/../Languages/de.php'))
            ->setLanguage($code);
    }

    public function comments(): CommentsService
    {
        return $this->instances[CommentsService::class] ??= new CommentsService(
            $this->dependencies->systemApi()->imageSketch()
        );
    }
}