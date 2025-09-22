<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfInput;

use Edutiek\AssessmentService\EssayTask\Essay\FullService as EssayService;
use Edutiek\AssessmentService\System\BackgroundTask\Manager as BackgroundTaskManager;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImage;
use Edutiek\AssessmentService\Task\CorrectorComment\FullService as CorrectorComment;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use DateTimeImmutable;
use Closure;
use Edutiek\AssessmentService\System\File\Storage;

readonly class Service implements FullService
{
    /**
     * @param Closure(int): CorrectorComment $get_corrector_comment
     */
    public function __construct(
        private EssayService $essay_service,
        private BackgroundTaskManager $task_manager,
        private EssayImage $essay_image,
        private Closure $get_corrector_comment,
        private Language $language,
        private Storage $storage,
    ) {
    }

    public function replacePdf(Essay $essay, string $file_id): void
    {
        $this->storage->deleteFile($essay->getPdfVersion());
        $this->essay_image->deleteByEssayId($essay->getId());
        $this->essay_service->save($essay->setPdfVersion($file_id)->touch());

        // todo: replace direct dependency by event handler
        ($this->get_corrector_comment)($essay->getTaskId(), $essay->getWriterId())->delete();

        // create page images in background task
        $this->task_manager->run(
            $this->language->txt('writer_upload_pdf_bt_processing'),
            GenerateEssayImages::class,
            $essay->getId()
        );
    }

    public function deletePdf($essay): void
    {
        $this->storage->deleteFile($essay->getPdfVersion());
        $this->essay_service->save($essay->setPdfVersion(null)->touch());
        $this->essay_image->deleteByEssayId($essay->getId());
    }
}
