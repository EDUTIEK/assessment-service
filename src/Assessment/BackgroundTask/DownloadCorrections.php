<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\BackgroundTask;

use Edutiek\AssessmentService\System\BackgroundTask\ComponentJob;
use Edutiek\AssessmentService\Assessment\PdfCreation\FullService as PdfCreation;
use Edutiek\AssessmentService\Assessment\Data\WritingTask;

readonly class DownloadCorrections implements ComponentJob
{
    public function __construct(
        private PdfCreation $pdf_creation
    ) {
    }

    public static function withDownload(): bool
    {
        return true;
    }

    public static function allowDelete(): bool
    {
        return true;
    }

    public function run($args): ?string
    {
        $ids = (array) $args[0];
        $anonymous_writer = (bool) $args[1];
        $anonymous_corrector = (bool) $args[2];

        $writings = [];
        foreach ($ids as $pair) {
            $writings[] = new WritingTask($pair[0], $pair[1]);
        }

        return $this->pdf_creation->createCorrectionZip($writings, $anonymous_writer, $anonymous_corrector);
    }
}
