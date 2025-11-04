<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreator;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverter;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Data\ImageSizeType;
use Exception;
use Generator;

class Service implements FullService
{
    private readonly string $temp_dir;

    public function __construct(
        private readonly PdfCreator $pdf_creator,
        private readonly PdfConverter $pdf_converter,
        private readonly Storage $storage,
        private readonly string $pdflatex_bin,
        private readonly string $pdftk_bin,
        string $temp_dir
    )
    {
        $this->temp_dir = rtrim($temp_dir, '/');
    }

    public function create(array $parts, array $meta_data = []): string
    {
        return $this->pdf_creator->createPdf(
            $parts,
            $meta_data['creator'] ?? '',
            $meta_data['author'] ?? '',
            $meta_data['title'] ?? '',
            $meta_data['subject'] ?? '',
            $meta_data['keywords'] ?? '',
        );
    }

    public function toImage($pdf, ConvertType $how, ImageSizeType $size = ImageSizeType::THUMBNAIL)
    {
        return $this->pdf_converter->{$how->value}($pdf, $size);
    }

    public function split(string $pdf_id, ?int $from = null, ?int $to = null): Generator
    {
        $in_file = $this->pathOfId($pdf_id);
        foreach (range(1, $this->count($pdf_id)) as $page) {
            $target = $this->storage->saveFile(fopen('php://memory', 'w+'))->getId();
            $this->exec(sprintf(
                'gs -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s -dFirstPage=%d -dLastPage=%d -sDEVICE=pdfwrite %s',
                escapeshellarg($this->pathOfId($target)),
                $page,
                $page,
                escapeshellarg($in_file)
            ));

            yield $target;
        }
    }

    public function join(array $pdf_ids): string
    {
        $target = $this->storage->saveFile(fopen('php://memory', 'w+'))->getId();
        $this->exec(sprintf(
            'gs -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dSAFER -sOutputFile=%s %s',
            escapeshellarg($this->pathOfId($target)),
            join(' ', array_map('escapeshellarg', array_map($this->pathOfId(...), $pdf_ids))),
        ));

        return $target;
    }

    public function count(string $pdf_id): int
    {
        // Cannot use escapeshellarg. The file is used inside the PS command and not as a standalone argument.
        // This means that it is currently vulnerable to command injecting attacks.
        return (int) current($this->exec(sprintf(
            'gs -q -dNOSAFER -dNODISPLAY -c "(%s) (r) file runpdfbegin pdfpagecount = quit"',
            $this->pathOfId($pdf_id)
        )));
    }

    public function number()
    {

    }

    public function nextToEachOther(string $pdf_left, string $pdf_right): string
    {
        $dir = $this->temp_dir . '/' . uniqid('edutiek-pdf-processing', true);
        mkdir($dir, 0700);
        $pdf_left = $this->pathOfId($pdf_left);
        $pdf_right = $this->pathOfId($pdf_right);
        copy($pdf_left, $dir . '/left.pdf');
        copy($pdf_right, $dir . '/right.pdf');

        $tex_filename = $dir . '/def.tex';
        $tex_stream = fopen($tex_filename, 'w+');
        fwrite($tex_stream, $this->template($pdf_right));
        $this->exec(sprintf(
            '%s -output-dir %s %s',
            escapeshellcmd($this->pdflatex_bin),
            escapeshellarg($dir),
            escapeshellarg($tex_filename),
        ));
        $id = $this->storage->saveFile(fopen($dir . '/def.pdf', 'rb'))->getId();
        $delme = ['def.pdf', 'left.pdf', 'right.pdf', 'def.aux', 'def.log', 'def.out', 'def.tex', 'pdfa.xmpi'];
        array_map(fn($f) => unlink($dir . '/' . $f), $delme);
        rmdir($dir);

        return $id;
    }

    public function onTopOfEachOther(string $pdf_top, string $pdf_bot): string
    {
        $pdf_top = $this->pathOfId($pdf_top);
        $pdf_bot = $this->pathOfId($pdf_bot);
        $target = $this->storage->saveFile(fopen('php://memory', 'w+'))->getId();

        $this->exec(sprintf(
            '%s %s stamp %s output %s 2>&1',
            escapeshellcmd($this->pdftk_bin),
            escapeshellarg($pdf_bot),
            escapeshellarg($pdf_top),
            escapeshellarg($this->pathOfId($target))
        ));

        return $target;
    }

    private function template(): string
    {
        return <<<END
\\batchmode
\\documentclass[a4paper,landscape]{article}
\\usepackage[utf8]{inputenc}
\\usepackage{pdfpages}
\\usepackage[a-2b,mathxmp]{pdfx}

\\begin{document}
\\includepdfmerge[nup=2x1]{left.pdf,-,right.pdf,-}
\\end{document}
END;
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
        $s = $this->storage->getFileStream($id);
        $path = stream_get_meta_data($s)['uri'];
        fclose($s);

        return $path;
    }
}
