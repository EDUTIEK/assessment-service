<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\BackgroundTask;

use Edutiek\AssessmentService\System\BackgroundTask\ComponentManager;
use Edutiek\AssessmentService\System\BackgroundTask\FullService as BackgroundTasks;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\BackgroundTask\ComponentJob;
use Edutiek\AssessmentService\EssayTask\Api\Internal;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;

readonly class Service implements ComponentManager, FullService
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private BackgroundTasks $manager,
        private Language $language,
        private Repositories $repos,
        private Internal $internal
    ) {
    }

    /**
     * Generate the page images of an essay
     */
    public function generateEssayImages(int $essay_id): void
    {
        $this->create($this->language->txt('generate_essay_images'), GenerateEssayImages::class, [$essay_id]);
    }

    public function create(string $title, string $job, array $args): void
    {
        $this->manager->create($title, 'essayTask', $job, [$this->ass_id, $this->user_id], [], $args);
    }

    public function run(string $job, array $args): ?string
    {
        return $this->getJob($job)?->run($args);
    }

    private function getJob(string $job): ?ComponentJob
    {
        switch ($job) {
            case GenerateEssayImages::class:
                return new GenerateEssayImages(
                    $this->repos->essay(),
                    $this->internal->essayImage($this->ass_id, $this->user_id)
                );
            default:
                return null;
        }
    }
}
