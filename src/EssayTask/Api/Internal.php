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
        $default_code = $this->dependencies->systemApi()->config()->getSetup()->getDefaultLanguage();
        $user_code = $this->dependencies->systemApi()->user()->getUser($user_id)?->getLanguage() ?? $default_code;

        $service = $this->instances[LanguageService::class][$user_id] = $this->dependencies->systemApi()->language()
            ->setDefaultLanguage($user_code)
            ->setLanguage($user_code);

        foreach (array_unique([$default_code, $user_code]) as $code) {
            if (file_exists(__DIR__ . '/../Languages/' . $code . '.php')) {
                $service->addLanguage($code, require(__DIR__ . '/../Languages/' . $code . '.php'));
            }
        }

        return $service;
    }

    public function comments(): CommentsService
    {
        return $this->instances[CommentsService::class] ??= new CommentsService(
            $this->dependencies->systemApi()->imageSketch()
        );
    }
}
