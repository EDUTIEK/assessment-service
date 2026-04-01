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
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettings;
use Edutiek\AssessmentService\System\Config\ReadService as Config;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\Spreadsheet\ExportType as SpreadsheetExportType;
use Edutiek\AssessmentService\System\Spreadsheet\FullService as Spreadsheets;
use Edutiek\AssessmentService\System\User\ReadService as UserService;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\Format\FullService as FormatService;
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
        private CorrectionSettings $correction_settings,
        private LogEntry $log,
        private ResultsExport $results_export,
        private Language $lang,
        private Config $config,
        private Spreadsheets $spreadsheets,
        private FileStorage $storage,
        private UserService $users,
        private FormatService $format
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

        $hashes = [];
        $hash_algo = $this->config->getConfig()->getHashAlgo();

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
                $source = $this->storage->getReadablePath($writing_pdf);
                $dest = $writer_dir . '/' . $this->pdf->buildPdfFilename([$writing], PdfPurpose::WRITING);
                $zip->addFile($source, $dest);
                $hashes[$dest] = hash($hash_algo, file_get_contents($source));

                $temp_files[] = $correction_pdf = $this->pdf->createCorrectionPdf($task_id, $writer_id, false, false);
                $source = $this->storage->getReadablePath($correction_pdf);
                $dest = $writer_dir . '/' . $this->pdf->buildPdfFilename([$writing], PdfPurpose::CORRECTION);
                $zip->addFile($source, $dest);
                $hashes[$dest] = hash($hash_algo, file_get_contents($source));
            }
        }

        if ($this->correction_settings->get()->getReportsEnabled()) {
            $temp_files[] = $report = $this->pdf->createCorrectionReport();
            $source = $this->storage->getReadablePath($report);
            $dest = $this->storage->asciiFilename($this->lang->txt('correction_reports') . '.pdf');
            $zip->addFile($source, $dest);
            $hashes[$dest] = hash($hash_algo, file_get_contents($source));
        }

        $temp_files[] = $results = $this->results_export->create();
        $source = $this->storage->getReadablePath($results);
        $dest = $this->storage->asciiFilename($this->lang->txt('result_export_filename') . '.csv');
        $zip->addFile($source, $dest);
        $hashes[$dest] = hash($hash_algo, file_get_contents($source));

        $temp_files[] = $log = $this->log->export(SpreadsheetExportType::CSV);
        $source = $this->storage->getReadablePath($log);
        $dest = $this->storage->asciiFilename($this->lang->txt('log_filename') . '.csv');
        $zip->addFile($source, $dest);
        $hashes[$dest] = hash($hash_algo, file_get_contents($source));

        $zip->close();
        foreach ($temp_files as $id) {
            $this->storage->deleteFile($id);
        }

        $suffix = $this->format->logDate(new \DateTime()) . '.zip';

        $fp = fopen($zipfile, 'r');
        $zip_name = $this->storage->asciiFilename($this->lang->txt('documentation_filename')
            . ' ' . $this->properties->get()->getTitle() . ' ' . $suffix);
        $info = $this->storage->saveFile(
            $fp,
            $this->storage->newInfo()
            ->setMimeType('application/zip')
            ->setFileName($zip_name)
        );
        $hashes[$zip_name] = hash($hash_algo, file_get_contents($zipfile));
        unlink($zipfile);


        $header = [
            'file' => $this->lang->txt('file'),
            'hash' => $this->lang->txt('hash') . ' (' . $hash_algo . ')',
        ];
        $rows = [];
        ksort($hashes);
        foreach ($hashes as $file => $hash) {
            $rows[] = [
                'file' => $file,
                'hash' => $hash,
            ];
        }
        $hash_id = $this->spreadsheets->dataToFile(
            $header,
            $rows,
            SpreadsheetExportType::CSV,
            $this->lang->txt('hash_filename') . ' ' . $suffix
        );

        $this->repos->exportFile()->save($this->repos->exportFile()->new()
            ->setAssId($this->ass_id)
            ->setFileId($info->getId())
            ->setType(ExportType::DOCUMENTATION));

        $this->repos->exportFile()->save($this->repos->exportFile()->new()
            ->setAssId($this->ass_id)
            ->setFileId($hash_id)
            ->setType(ExportType::HASHES));

        return $info->getId();
    }
}
