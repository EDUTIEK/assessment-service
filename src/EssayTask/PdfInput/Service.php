<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfInput;

use Edutiek\AssessmentService\EssayTask\Essay\FullService as EssayService;
use Edutiek\AssessmentService\System\BackgroundTask\Manager as BackgroundTaskManager;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImage;
use Edutiek\AssessmentService\EssayTask\CorrectorComment\FullService as CorrectorComment;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use DateTimeImmutable;
use Closure;

class Service implements FullService
{
    /**
     * @param Closure(int): CorrectorComment
     */
    public function __construct(
        private readonly EssayService $essay,
        private readonly BackgroundTaskManager $task_manager,
        private readonly EssayImage $essay_image,
        private readonly Closure $get_corrector_comment,
        private readonly Language $language,
    )
    {
    }

    public function handleInput(Essay $essay): void
    {
        $now = new DateTimeImmutable();
        if (!$essay->getFirstChange()) {
            $essay->setFirstChange($now);
            $this->essay->save($essay);
        }
        if(!$essay->getLastChange()) {
            $essay->setFirstChange($now);
        }
        $this->essay->save($essay);

        $this->essay_image->deleteByEssayId($essay->getId());
        ($this->get_corrector_comment)($essay->getId())->deleteByEssayId($essay->getId());

        // create page images in background task
        if ($essay->getPdfVersion() !== null) {
            $this->task_manager->run(
                $this->language->txt('writer_upload_pdf_bt_processing'),
                GenerateEssayImages::class,
                $essay->getId()
            );
        }
    }
}
