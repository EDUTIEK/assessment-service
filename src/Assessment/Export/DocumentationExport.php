<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\ExportType;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Properties\FullService as Properties;
use Edutiek\AssessmentService\Assessment\LogEntry\FullService as LogEntry;
use Edutiek\AssessmentService\Assessment\PdfCreation\FullService as PdfService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPurpose;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\Assessment\WritingTask\ReadService as WritingTasks;
use Edutiek\AssessmentService\System\Config\ReadService as Config;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType as SpreadsheetExportType;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as Spreadsheets;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use ZipArchive;

class DocumentationExport
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private Properties $properties,
        private WriterService $writers,
        private TaskManager $tasks,
        private PdfService $pdf,
        private WritingTasks $writing_tasks,
        private LogEntry $log,
        private ResultsExport $results_export,
        private Language $lang,
        private Config $config,
        private Spreadsheets $spreadsheets,
        private FileStorage $storage,
        private UserService $users
    ) {
    }

    /**
     * Create a documentation export file
     * @return string storage file_id
     */
    public function create(): string
    {
        $tasks = [];
        foreach ($this->tasks->all() as $task) {
            $tasks[$task->getId()] = $task;
        }
        $multi_tasks = count($tasks) > 1;

        $writers = [];
        $user_ids = [];
        foreach ($this->writers->all() as $writer) {
            $writers[$writer->getId()] = $writer;
            $user_ids[] = $writer->getUserId();
        }
        $users = $this->users->getUsersByIds($user_ids);

        $writings_by_ids = [];
        foreach ($this->writing_tasks->all() as $writing) {
            $writings_by_ids[$writing->getWriterId()][$writing->getTaskId()] = $writing;
        }

        $zipfile = $this->config->getSetup()->getAbsoluteTempPath()
            . uniqid('', true) . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipfile, ZipArchive::CREATE);

        $temp_files = [];
        foreach ($writings_by_ids as $writer_id => $writings) {
            $writer = $writers[$writer_id];
            $user = $users[$writer?->getUserId()] ?? null;
            $writer_dir = $this->storage->asciiFilename($user?->getListname(true));
            $zip->addEmptyDir($writer_dir);

            foreach ($writings as $task_id => $writing) {
                $temp_files[] = $writing_pdf = $this->pdf->createWritingPdf($task_id, $writer_id, false);
                $zip->addFile(
                    $this->storage->getReadablePath($writing_pdf),
                    $writer_dir . '/' . $this->pdf->buildPdfFilename([$writing], PdfPurpose::WRITING)
                );

                $temp_files[] = $correction_pdf = $this->pdf->createCorrectionPdf($task_id, $writer_id, false, false);
                $zip->addFile(
                    $this->storage->getReadablePath($correction_pdf),
                    $writer_dir . '/' . $this->pdf->buildPdfFilename([$writing], PdfPurpose::CORRECTION)
                );
            }
        }

        $temp_files[] = $results = $this->results_export->create();
        $zip->addFile($this->storage->getReadablePath($results), $this->lang->txt('result_export_filename') . '.csv');

        $temp_files[] = $log = $this->log->export(SpreadsheetExportType::CSV);
        $zip->addFile($this->storage->getReadablePath($log), $this->lang->txt('log_filename') . '.csv');

        $zip->close();
        foreach ($temp_files as $id) {
            $this->storage->deleteFile($id);
        }

        $fp = fopen($zipfile, 'r');
        $info = $this->storage->saveFile(
            $fp,
            $this->storage->newInfo()
            ->setMimeType('application/zip')
            ->setFileName($this->storage->asciiFilename(
                $this->lang->txt('documentation_filename') . ' ' . $this->properties->get()->getTitle()
            ))
        );
        unlink($zipfile);

        $file = $this->repos->exportFile()->new()
            ->setAssId($this->ass_id)
            ->setFileId($info->getId())
            ->setType(ExportType::DOCUMENTATION);
        $this->repos->exportFile()->save($file);

        return $info->getId();
    }
}
