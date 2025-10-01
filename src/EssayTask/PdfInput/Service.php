<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfInput;

use Edutiek\AssessmentService\EssayTask\Api\ApiException;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Essay\FullService as EssayService;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImage;
use Edutiek\AssessmentService\System\BackgroundTask\Manager as BackgroundTaskManager;
use Edutiek\AssessmentService\System\ConstraintHandling\Actions\ChangeWritingContent;
use Edutiek\AssessmentService\System\ConstraintHandling\Collector;
use Edutiek\AssessmentService\System\ConstraintHandling\Result;
use Edutiek\AssessmentService\System\ConstraintHandling\ResultStatus;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingContentChanged;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;

readonly class Service implements FullService
{
    public function __construct(
        private bool $as_admin,
        private EssayService $essay_service,
        private BackgroundTaskManager $task_manager,
        private EssayImage $essay_image,
        private Language $language,
        private Storage $storage,
        private Dispatcher $events,
        private Collector $constraints
    ) {
    }

    public function checkReplacePdf(Essay $essay): Result
    {
        return $this->constraints->check(new ChangeWritingContent(
            $essay->getWriterId(),
            $essay->getTaskId(),
            $this->as_admin
        ));
    }

    public function replacePdf(Essay $essay, string $file_id): void
    {
        $result = $this->checkDeletePdf($essay);
        if ($result->status() == ResultStatus::BLOCK) {
            throw new ApiException(implode("/n", $result->messages()), ApiException::CONSTRAINT);
        }

        $this->storage->deleteFile($essay->getPdfVersion());
        $this->essay_image->deleteByEssayId($essay->getId());
        $this->essay_service->save($essay->setPdfVersion($file_id)->touch());
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

    public function checkDeletePdf(Essay $essay): Result
    {
        return $this->constraints->check(new ChangeWritingContent(
            $essay->getWriterId(),
            $essay->getTaskId(),
            $this->as_admin
        ));
    }

    public function deletePdf(Essay $essay): void
    {
        $result = $this->checkDeletePdf($essay);
        if ($result->status() == ResultStatus::BLOCK) {
            throw new ApiException(implode("/n", $result->messages()), ApiException::CONSTRAINT);
        }

        $this->storage->deleteFile($essay->getPdfVersion());
        $this->essay_service->save($essay->setPdfVersion(null)->touch());
        $this->events->dispatchEvent(new WritingContentChanged(
            $essay->getWriterId(),
            $essay->getTaskId(),
            $essay->getLastChange()
        ));
        $this->essay_image->deleteByEssayId($essay->getId());
    }
}
