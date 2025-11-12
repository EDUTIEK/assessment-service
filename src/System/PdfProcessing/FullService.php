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
     * @param PdfElement[] $elements
     */
    public function createFromParts(array $elements, Options $options): string;

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
    public function number(string $pdf_id, int $start_page_number = 1): array;

        /**
     * Many "trash" files are created when using split, join, number, etc,
     * to have them deleted automatically use this method.
     * To keep files use the provided $keep_file argument:
     *
     * return $this->cleanUpTrashFiles(function($keep_file){
     *     return $keep_file($this->join($this->number($this->create('huhu', new Options()))));
     * });
     *
     * The above example deletes all generated files except the returned one.
     *
     *
     * @template A
     *
     * @param callable(callable(string): string): A
     * @return A
     */
    public function cleanUpTrashFiles(callable $thunk);
}
