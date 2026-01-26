<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

use Edutiek\AssessmentService\System\PdfCreator\PdfPart;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\Data\ImageSizeType;
use Generator;
use Edutiek\AssessmentService\System\PdfCreator\Options;

interface FullService
{
    public function create(string $html, Options $options): string;

    /**
     * Create page images of a PDF file
     * @param resource $pdf
     * @return array<ImageDescriptor>|ImageDescriptor|null
     */
    public function toImage($pdf, ConvertType $how, ImageSizeType $size = ImageSizeType::THUMBNAIL);

    /**
     * Split a PDF file into parts
     * @param string $pdf_id
     * @return Generator<string>
     */
    public function split(string $pdf_id, ?int $from = null, ?int $to = null): Generator;

    /**
     * Join separate PDF files into one
     * @param string[] $pdf_ids
     * @return string
     */
    public function join(array $pdf_ids): string;

    /**
     * Count the pages of a pdf file
     * @param resource $pdf
     */
    public function count(string $pdf_id): int;

    /**
     * @param resource $pdf_left
     * @param resource $pdf_right
     */
    public function nextToEachOther(string $pdf_left, string $pdf_right): string;

    /**
     * @param resource $pdf_left
     * @param resource $pdf_right
     */
    public function onTopOfEachOther(string $pdf_left, string $pdf_right): string;

    /**
     * Create page numbers in a pdf file
     * @todo
     */
    public function number(string $pdf_id, int $start_page_number = 1): string;

    /**
     * Cleanup temporary files created during the processing
     * This should be called after the last processing step of a sequence
     *
     * @param string[] $keep_ids    file ids of files that should be kept
     */
    public function cleanupExcept(array $keep_ids);
}
