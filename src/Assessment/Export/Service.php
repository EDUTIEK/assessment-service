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
use Edutiek\AssessmentService\Assessment\LogEntry\FullService as LogEntryService;
use Edutiek\AssessmentService\System\Data\FileInfo;
use Edutiek\AssessmentService\System\File\Disposition;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\File\Delivery as FileDelivery;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\ExportSettings;
use Edutiek\AssessmentService\Assessment\Data\ExportType;
use Edutiek\AssessmentService\Assessment\Data\ExportFile;
use SplFileInfo;

class Service implements FullService
{
    private ?array $task_ids = null;
    private ?array $writer_ids = null;

    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private PdfCreation $pdf,
        private BackgroundTaskService $background_tasks,
        private PropertiesService $properties,
        private TaskManager $tasks,
        private WriterService $writers,
        private FileStorage $storage,
        private FileDelivery $delivery,
        private Language $lang,
        private ResultsExport $results,
        private LogEntryService $log
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
                $this->buildPdfFilename($writings, PdfPurpose::WRITING)
            );
            return true;
        }

        $wt = reset($writings);
        $file_id = $this->pdf->createWritingPdf(
            $wt->getTaskId(),
            $wt->getWriterId(),
            $anonymous
        );

        $temp_file = $this->storage->copyAsTempFile($file_id);

        $this->storage->deleteFile($file_id);
        $this->delivery->sendTempFile(
            $temp_file,
            Disposition::ATTACHMENT,
            $this->storage->newInfo()
                          ->setFileName($this->buildPdfFilename($writings, PdfPurpose::WRITING))
                          ->setMimeType('application/pdf')
        );

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
                $this->buildPdfFilename($writings, PdfPurpose::CORRECTION)
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

        $temp_file = $this->storage->copyAsTempFile($file_id);

        $this->storage->deleteFile($file_id);
        $this->delivery->sendTempFile(
            $temp_file,
            Disposition::ATTACHMENT,
            $this->storage->newInfo()
                          ->setFileName($this->buildPdfFilename($writings, PdfPurpose::CORRECTION))
                          ->setMimeType('application/pdf')
        );

        return false;
    }

    /**
     * @param WritingTask[] $writings
     */
    private function buildPdfFilename($writings, PdfPurpose $purpose): string
    {
        if (count($writings) > 1) {
            $filename = $this->properties->get()->getTitle()
                . ' - ' . $this->lang->txt(
                    match ($purpose) {
                        PdfPurpose::WRITING => 'writings',
                        PdfPurpose::CORRECTION => 'corrections',
                    }
                )
                . '.zip';
        } else {
            $wt = reset($writings);
            $task = $this->tasks->one($wt->getTaskId());
            $writer = $this->writers->oneByWriterId($wt->getWriterId());

            $filename = $this->properties->get()->getTitle()
                . ($this->tasks->count() > 1 ? ' - ' . $this->tasks->one($wt->getTaskId())->getTitle() : '')
                . ' - ' . $writer->getPseudonym()
                . ' - ' . $this->lang->txt(
                    match ($purpose) {
                        PdfPurpose::WRITING => 'writing',
                        PdfPurpose::CORRECTION => 'correction',
                    }
                )
                . '.pdf';
        }

        return $this->storage->asciiFilename($filename);
    }

    public function getSettings(): ExportSettings
    {
        return $this->repos->exportSettings()->one($this->ass_id) ?? $this->repos->exportSettings()->new()->setAssId($this->ass_id);
    }

    public function saveSettings(ExportSettings $settings)
    {
        $this->repos->exportSettings()->save($settings);
    }

    public function createFile(ExportType $type): bool
    {
        $file_id = '';
        switch ($type) {
            case ExportType::DOCUMENTATION:
                return true;

            case ExportType::RESULTS:
                $file_id = $this->results->create();
                break;

            case ExportType::LOG:
                $file_id = $this->log->export();
                break;

            case ExportType::REPORTS:
                return false;
        }

        $file = $this->repos->exportFile()->new()
                            ->setAssId($this->ass_id)
                            ->setFileId($file_id)
                            ->setType($type);
        $this->repos->exportFile()->save($file);

        return false;
    }

    public function getFiles(): array
    {
        return $this->repos->exportFile()->allByAssId($this->ass_id);
    }

    public function getFilesByIds(array $file_ids): array
    {
        return $this->repos->exportFile()->allByAssIdAndFileIds($this->ass_id, $file_ids);
    }

    /**
     * @param string[] $file_ids
     * @return \SplFileInfo[]
     */
    public function getFilesInfos(array $file_ids): array
    {
        $infos = [];
        foreach ($this->getFilesByIds($file_ids) as $file) {
            $infos[] = new SplFileInfo($this->storage->getReadablePath($file->getFileId()));
        }
        return $infos;
    }


    public function addFile(string $file_id, ExportType $type): void
    {
        $file = $this->repos->exportFile()->new()
            ->setAssId($this->ass_id)
            ->setFileId($file_id)
            ->setType($type);
        $this->repos->exportFile()->save($file);
    }

    public function deleteFile(ExportFile $file): void
    {
        if ($this->repos->exportFile()->hasByAssIdAndFileId($this->ass_id, $file->getFileId())) {
            $this->repos->exportFile()->delete($file->getId());
            $this->storage->deleteFile($file->getFileId());
        }
    }

    public function downloadFile(ExportFile $file): void
    {
        if ($this->repos->exportFile()->hasByAssIdAndFileId($this->ass_id, $file->getFileId())) {
            $this->delivery->sendFile($file->getFileId(), Disposition::ATTACHMENT);
        }
    }
}
