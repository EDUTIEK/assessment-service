<?php

namespace Edutiek\AssessmentService\EssayTask\Essay;

use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\EssayTask\Api\ApiException;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImage;
use Edutiek\AssessmentService\System\BackgroundTask\Manager as BackgroundTaskManager;
use Edutiek\AssessmentService\System\ConstraintHandling\Actions\ChangeWritingContent;
use Edutiek\AssessmentService\System\ConstraintHandling\Collector;
use Edutiek\AssessmentService\System\ConstraintHandling\ConstraintResult;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultStatus;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingContentChanged;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Task\Manager\ReadService as TasksReadService;

readonly class Service implements ClientService, EventService
{
    public function __construct(
        private bool $as_admin,
        private Repositories $repos,
        private WriterReadService $writer_service,
        private TasksReadService $tasks,
        private BackgroundTaskManager $task_manager,
        private EssayImage $essay_image,
        private Language $language,
        private Storage $storage,
        private Dispatcher $events,
        private Collector $constraints
    ) {
    }

    public function allByWriterId(int $writer_id): array
    {
        $this->checkWriterScope($writer_id);
        return $this->repos->essay()->allByWriterId($writer_id);
    }

    public function allByTaskId(int $task_id): array
    {
        $this->checkTaskScope($task_id);
        return $this->repos->essay()->allByTaskId($task_id);
    }

    public function getByWriterId(int $writer_id): array
    {
        $essays = [];
        foreach ($this->tasks->all() as $task_info) {
            $essays[$task_info->getId()] =
               $this->getByWriterIdAndTaskId($writer_id, $task_info->getId());
        }
        return $essays;
    }

    public function getByWriterIdAndTaskId(int $writer_id, int $task_id): Essay
    {
        $this->checkWriterScope($writer_id);
        $this->checkTaskScope($task_id);
        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
        if ($essay === null) {
            $essay = $this->repos->essay()->new()
                ->setWriterId($writer_id)
                ->setTaskId($task_id);
            $this->repos->essay()->save($essay);
        }
        return $essay;
    }

    public function oneByWriterIdAndTaskId(int $writer_id, int $task_id): ?Essay
    {
        $this->checkWriterScope($writer_id);
        $this->checkTaskScope($task_id);
        return $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
    }

    public function new(int $writer_id, int $task_id): Essay
    {
        $this->checkWriterScope($writer_id);
        $this->checkTaskScope($task_id);
        $essay = $this->repos->essay()->new()->setWriterId($writer_id)->setTaskId($task_id);
        return $essay;
    }

    public function save(Essay $essay): void
    {
        $this->checkWriterScope($essay->getWriterId());
        $this->checkTaskScope($essay->getTaskId());
        $this->repos->essay()->save($essay);
    }

    /**
     * Check if the writer belongs to the assessment
     */
    private function checkWriterScope(int $writer_id)
    {
        if (!$this->writer_service->has($writer_id)) {
            throw new ApiException("wrong writer_id", ApiException::ID_SCOPE);
        }
    }

    /**
     * Check if the task belongs to the assessment
     */
    private function checkTaskScope(int $task_id)
    {
        if (!$this->tasks->has($task_id)) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }

    /**
     * Check if the content of an essay can be replaced
     */
    public function canChange(Essay $essay): ConstraintResult
    {
        $this->checkWriterScope($essay->getWriterId());
        $this->checkTaskScope($essay->getTaskId());

        return $this->constraints->check(new ChangeWritingContent(
            $essay->getWriterId(),
            $essay->getTaskId(),
            $this->as_admin
        ));
    }

    public function replacePdf(Essay $essay, string $file_id): void
    {
        $result = $this->canChange($essay);
        if ($result->status() == ResultStatus::BLOCK) {
            throw new ApiException(implode("/n", $result->messages()), ApiException::CONSTRAINT);
        }

        $this->storage->deleteFile($essay->getPdfVersion());
        $this->essay_image->deleteByEssayId($essay->getId());
        $this->repos->essay()->save($essay->setPdfVersion($file_id)->touch());
        $this->events->dispatchEvent(new WritingContentChanged(
            $essay->getWriterId(),
            $essay->getTaskId(),
            $essay->getLastChange()
        ));

        // create page images in background task
        $this->task_manager->run(
            $this->language->txt('writer_upload_pdf_bt_processing'),
            GenerateEssayImages::class,
            $essay->getId()
        );
    }

    public function deletePdf(Essay $essay): void
    {
        $result = $this->canChange($essay);
        if ($result->status() == ResultStatus::BLOCK) {
            throw new ApiException(implode("/n", $result->messages()), ApiException::CONSTRAINT);
        }

        $this->storage->deleteFile($essay->getPdfVersion());
        $this->repos->essay()->save($essay->setPdfVersion(null)->touch());
        $this->events->dispatchEvent(new WritingContentChanged(
            $essay->getWriterId(),
            $essay->getTaskId(),
            $essay->getLastChange()
        ));
        $this->essay_image->deleteByEssayId($essay->getId());
    }

    /**
     * This function is called from an event handler
     * No constraints to be checked
     */
    public function delete(Essay $essay): void
    {
        $this->storage->deleteFile($essay->getPdfVersion());
        $this->essay_image->deleteByEssayId($essay->getId());
        $this->repos->essay()->delete($essay->getId());
        $this->repos->writerNotice()->deleteByEssayId($essay->getId());
        $this->repos->writingStep()->deleteByEssayId($essay->getId());
    }
}
