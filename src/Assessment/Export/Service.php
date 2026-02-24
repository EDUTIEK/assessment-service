<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\Assessment\BackgroundTask\FullService as BackgroundTaskService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPurpose;
use Edutiek\AssessmentService\Assessment\Properties\ReadService as PropertiesService;
use Edutiek\AssessmentService\Assessment\PdfCreation\FullService as PdfCreation;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;
use Edutiek\AssessmentService\System\Data\FileInfo;
use Edutiek\AssessmentService\System\File\Disposition;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\File\Delivery as FileDelivery;
use Edutiek\AssessmentService\System\Language\FullService as Language;

class Service implements FullService
{
    private ?array $task_ids = null;
    private ?array $writer_ids = null;

    public function __construct(
        private PdfCreation $pdf,
        private BackgroundTaskService $background_tasks,
        private PropertiesService $properties,
        private TaskManager $tasks,
        private WriterService $writers,
        private FileStorage $storage,
        private FileDelivery $delivery,
        private Language $lang,
    ) {
    }

    /**
     * @param WritingTask[] $writings
     */
    public function downloadWritings(array $writings, bool $anonymous): bool
    {
        if (count($writings) > 1) {
            $this->background_tasks->downloadWritings(
                $writings,
                $anonymous,
                $this->buildFilename($writings, PdfPurpose::WRITING)
            );
            return true;
        }

        $wt = reset($writings);
        $file_id = $this->pdf->createWritingPdf(
            $wt->getTaskId(),
            $wt->getWriterId(),
            $anonymous
        );

        $this->delivery->sendFile(
            $file_id,
            Disposition::ATTACHMENT,
            $this->storage->newInfo()
                ->setFileName($this->buildFilename($writings, PdfPurpose::WRITING))
                ->setMimeType('application/pdf')
        );

        $this->delivery->sendFile($file_id, Disposition::ATTACHMENT);
        $this->storage->deleteFile($file_id);
        return false;
    }

    /**
     * @param WritingTask[] $writings
     */
    public function downloadCorrections(array $writings, bool $anonymous_writer, bool $anonymous_corrector): bool
    {
        if (count($writings) > 1) {
            $this->background_tasks->downloadCorrections(
                $writings,
                $anonymous_writer,
                $anonymous_corrector,
                $this->buildFilename($writings, PdfPurpose::CORRECTION)
            );
            return true;
        }

        $wt = reset($writings);
        $file_id = $this->pdf->createCorrectionPdf(
            $wt->getTaskId(),
            $wt->getWriterId(),
            $anonymous_writer,
            $anonymous_corrector
        );

        $this->delivery->sendFile(
            $file_id,
            Disposition::ATTACHMENT,
            $this->storage->newInfo()
                ->setFileName($this->buildFilename($writings, PdfPurpose::CORRECTION))
                ->setMimeType('application/pdf')
        );
        $this->storage->deleteFile($file_id);
        return false;
    }

    /**
     * @param WritingTask[] $writings
     */
    private function buildFilename($writings, PdfPurpose $purpose): string
    {
        if (count($writings) > 1) {
            $filename = $this->properties->get()->getTitle()
                . ' - ' . $this->lang->txt(match($purpose) {
                    PdfPurpose::WRITING => 'writings',
                    PdfPurpose::CORRECTION => 'corrections',
                })
                . '.zip';
        } else {
            $wt = reset($writings);
            $task = $this->tasks->one($wt->getTaskId());
            $writer = $this->writers->oneByWriterId($wt->getWriterId());

            $filename = $this->properties->get()->getTitle()
                . ($this->tasks->count() > 1 ? ' - ' . $this->tasks->one($wt->getTaskId())->getTitle() : '')
                . ' - ' . $writer->getPseudonym()
                . ' - ' . $this->lang->txt(match($purpose) {
                    PdfPurpose::WRITING => 'writing',
                    PdfPurpose::CORRECTION => 'correction',
                })
                . '.pdf';
        }

        return $this->storage->asciiFilename($filename);
    }
}
