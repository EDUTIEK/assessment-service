<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\BackgroundTask;

use Edutiek\AssessmentService\System\BackgroundTask\ComponentManager;
use Edutiek\AssessmentService\System\BackgroundTask\FullService as BackgroundTasks;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Assessment\Properties\ReadService as PropertiesService;
use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\System\BackgroundTask\ComponentJob;

readonly class Service implements ComponentManager, FullService
{
    public function __construct(
        private int $ass_id,
        private int $context_id,
        private int $user_id,
        private PropertiesService $properties,
        private BackgroundTasks $manager,
        private language $language,
        private Internal $internal,
    ) {
    }

    /**
     * @param WritingTask[] $writings
     */
    public function downloadCorrections(array $writings, bool $anonymous_writer, bool $anonymous_corrector): void
    {
        $ids = [];
        foreach ($writings as $writing) {
            $ids[] = [$writing->getWriterId(), $writing->getTaskId()];
        }

        $this->create(
            $this->language->txt('download_corrections', ['title' => $this->properties->get()->getTitle()]),
            DownloadCorrections::class,
            [$ids, $anonymous_writer, $anonymous_corrector]
        );
    }

    public function create(string $title, string $job, array $args): void
    {
        $this->manager->create($title, 'assessment', $job, [$this->ass_id, $this->user_id], [$this->context_id], $args);
    }

    public function run(string $job, array $args): ?string
    {
        return $this->getJob($job)?->run($args);
    }

    private function getJob(string $job): ?ComponentJob
    {
        switch ($job) {
            case DownloadCorrections::class:
                return new DownloadCorrections(
                    $this->internal->pdfCreation($this->ass_id, $this->context_id, $this->user_id)
                );
            default:
                return null;
        }
    }

}
