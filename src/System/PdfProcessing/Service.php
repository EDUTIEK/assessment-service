<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreator;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverter;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Data\ImageSizeType;
use Exception;
use Generator;
use Edutiek\AssessmentService\System\PdfCreator\Options;
use Com\Tecnick\Pdf\Parser\Parser;
use Edutiek\AssessmentService\System\Log\FullService as Logger;

class Service implements FullService
{
    private readonly string $temp_dir;
    private ?array $saved_files = [];

    public function __construct(
        private readonly PdfCreator $pdf_creator,
        private readonly Storage $storage,
        private readonly Logger $logger,
        private readonly string $ghostscript_bin,
        private readonly string $pdftk_bin,
        string $temp_dir
    ) {
        $this->temp_dir = rtrim($temp_dir, '/');
    }

    public function create(string $html, Options $options): string
    {
        $file = tempnam($this->temp_dir, 'tmp') . '.pdf';
        $stream = fopen($file, 'w+');
        fwrite($stream, $this->pdf_creator->createPdf($html, $options));
        $file_id = $this->saveFile($stream);
        return $file_id;
    }

    public function join(array $pdf_ids): string
    {
        try {
            return $this->joinAcc($pdf_ids);
        } catch (\Exception $e) {
            $this->logger->error('Falling back to GS, PDFUnite exception: ' . $e->getMessage());
            return $this->joinGS($pdf_ids);
        }
    }

    private function joinGS(array $pdf_ids): string
    {
        $target = $this->saveFile(fopen('php://memory', 'w+'));
        $this->exec(sprintf(
            '%s -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dSAFER -sOutputFile=%s %s',
            escapeshellcmd($this->ghostscript_bin),
            escapeshellarg($this->pathOfId($target)),
            join(' ', array_map('escapeshellarg', array_map($this->pathOfId(...), $pdf_ids))),
        ));

        return $target;
    }

    private function joinAcc(array $pdf_ids): string
    {
        $pdf_string = file_get_contents($this->pathOfId($pdf_ids[0]));
        $header = PdfUnite::parseHeader($pdf_string);
        $doc = (new Parser(['ignore_filter_errors' => true]))->parse($pdf_string);

        foreach (array_slice($pdf_ids, 1) as $file_id) {
            $src = (new Parser(['ignore_filter_errors' => true]))->parse(file_get_contents($this->pathOfId($file_id)));
            $c = new PdfUnite($src, $doc);
            $c->copyPages();
            $doc = $c->writeBack();
        }

        $target = $this->saveFile(fopen('php://memory', 'w+'));
        $fd = fopen($this->pathOfId($target), 'wb');
        (new PdfWriter($fd))->write($header, $doc);
        fclose($fd);

        return $target;
    }

    public function copy(string $pdf_id): string
    {
        return $this->storage->saveFile(
            $this->storage->getFileStream($pdf_id),
            $this->storage->getFileInfo($pdf_id)->setId(null)
        )->getId();
    }

    public function count(string $pdf_id): int
    {
        // Cannot use escapeshellarg. The file is used inside the PS command and not as a standalone argument.
        // This means that it is currently vulnerable to command injecting attacks.
        return (int) current($this->exec(sprintf(
            '%s -q -dNOSAFER -dNODISPLAY -c "(%s) (r) file runpdfbegin pdfpagecount = quit"',
            escapeshellcmd($this->ghostscript_bin),
            $this->pathOfId($pdf_id)
        )));
    }

    public function onTopOfEachOther(string $pdf_top, string $pdf_bot): string
    {
        $pdf_top = $this->pathOfId($pdf_top);
        $pdf_bot = $this->pathOfId($pdf_bot);
        $target = $this->saveFile(fopen('php://memory', 'w+'));

        $this->exec(sprintf(
            '%s %s stamp %s output %s 2>&1',
            escapeshellcmd($this->pdftk_bin),
            escapeshellarg($pdf_bot),
            escapeshellarg($pdf_top),
            escapeshellarg($this->pathOfId($target))
        ));

        return $target;
    }

    public function cleanup(array $ids)
    {
        return;
        foreach (array_intersect($this->saved_files, $ids) as $id) {
            $this->storage->deleteFile($id);
        }
        $this->saved_files = array_diff($this->saved_files, $ids);
    }

    public function cleanupExcept(array $keep_ids)
    {
        return;
        foreach (array_diff($this->saved_files, $keep_ids) as $id) {
            $this->storage->deleteFile($id);
        }
        $this->saved_files = array_intersect($this->saved_files, $keep_ids);
    }

    /**
     * @return string[]
     */
    private function exec(string $cmd): array
    {
        exec($cmd, $lines, $exit_code);

        if ($exit_code !== 0) {
            throw new Exception('Failed to run: ' . $cmd . join(PHP_EOL, $lines));
        }

        return $lines;
    }

    private function pathOfId(string $id): string
    {
        return $this->storage->getReadablePath($id);
    }

    private function saveFile($content): string
    {
        $id = $this->storage->saveFile(
            $content,
            $this->storage->newInfo()
            ->setMimeType('application/pdf')
            ->setFileName('file.pdf')
        )->getId();
        $this->saved_files[] = $id;
        return $id;
    }
}
