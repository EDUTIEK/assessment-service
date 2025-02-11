<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\Comments\FullService as CommentsFullService;
use Edutiek\AssessmentService\EssayTask\Comments\Service as CommentsService;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlFullService;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service as HtmlService;

class ForServices
{
    private array $instances = [];

    public function __construct(
        private readonly int $task_id,
        private readonly Dependencies $dependencies
    ) {
    }

    public function htmlProcessing(): HtmlFullService
    {
        return $this->instances[CommentsFullService::class] ??= new HtmlService(
            $this->comments()
        );
    }

    public function comments(): CommentsFullService
    {
        return $this->instances[CommentsFullService::class] ??= new CommentsService(
            $this->dependencies->systemApi()->imageSketch()
        );
    }


}