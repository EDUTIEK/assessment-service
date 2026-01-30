<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Export;

use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\Assessment\PdfCreation\FullService as PdfCreation;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager;
use Edutiek\AssessmentService\System\Data\FileInfo;
use Edutiek\AssessmentService\System\File\Disposition;
use Edutiek\AssessmentService\System\File\Storage as FileStorage;
use Edutiek\AssessmentService\System\File\Delivery as FileDelivery;

class Service implements FullService
{
    private ?array $task_ids = null;
    private ?array $writer_ids = null;

    public function __construct(
        private PdfCreation $pdf,
        private TaskManager $tasks,
        private WriterService $writers,
        private FileStorage $storage,
        private FileDelivery $delivery,
    ) {
    }

    public function downloadWritings(array $writings, bool $anonymous): void
    {
        if (count($writings) > 1) {
            $file_id = $this->pdf->createWritingZip($writings, $anonymous);
            $filename = 'writings.zip';
            $mimetype = 'application/zip';
        } else {
            $wt = reset($writings);
            $file_id = $this->pdf->createWritingPdf($wt->getTaskId(), $wt->getWriterId(), $anonymous);
            $filename = 'task' . $wt->getWriterId() . '_writer' . $wt->getWriterId() . '-writing.pdf';
            $mimetype = 'application/pdf';
        }

        $this->delivery->sendFile(
            $file_id,
            Disposition::ATTACHMENT,
            $this->storage->newInfo()->setFileName($filename)->setMimeType($mimetype)
        );
        $this->storage->deleteFile($file_id);
    }


    private function checkScope(WritingTask $writing_task): void
    {

    }
}
