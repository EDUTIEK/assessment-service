<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfProcessing;

use Edutiek\AssessmentService\System\PdfCreator\PdfPart;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\Data\ImageSizeType;
use Generator;

interface FullService
{
    /**
     * @param PdfPart[] $parts
     * @param array<string, string> $meta_data
     */
    public function create(array $parts, array $meta_data = []): string;

    /**
     * @param resource $pdf
     * @return array<ImageDescriptor>|ImageDescriptor|null
     */
    public function toImage($pdf, ConvertType $how, ImageSizeType $size = ImageSizeType::THUMBNAIL);

    /**
     * @param string $pdf_id
     * @return Generator<string>
     */
    public function split(string $pdf_id, ?int $from = null, ?int $to = null): Generator;

    /**
     * @param string[] $pdf_ids
     * @return string
     */
    public function join(array $pdf_ids): string;

    /**
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
     * @todo
     */
    public function number();
}
