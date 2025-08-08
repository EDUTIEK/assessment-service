<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\PdfOutput;

use Edutiek\AssessmentService\Assessment\PdfSettings\FullService as PdfSettingsService;
use Edutiek\AssessmentService\EssayTask\Data\EssayImageRepo;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreator;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\PdfCreator\PdfImage;
use Edutiek\AssessmentService\System\PdfCreator\PdfHtml;
use Edutiek\AssessmentService\System\Format\FullService as Format;
use Edutiek\AssessmentService\EssayTask\Data\EssayRepo;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImageService;
use Edutiek\AssessmentService\EssayTask\Data\EssayImage;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService as WritingSettings;
use Edutiek\AssessmentService\EssayTask\Data\Essay;

class Service implements FullService
{
    public function __construct(
        private readonly PdfSettingsService $pdf_settings,
        private readonly EssayImageRepo $essay_image,
        private readonly PdfCreator $pdf_creator,
        private readonly Storage $storage,
        private readonly HtmlProcessing $html_processing,
        private readonly WritingSettings $writing_settings,
        private readonly Format $format,
        private readonly EssayImageService $essay_image_service,
    ) {
    }

    public function getWritingAsPdf(Essay $essay, bool $plainContent = false, bool $onlyText = false) : string
    {
        $pdfSettings = $plainContent ? null : $this->pdf_settings->get();

        $pdfParts = [];
        $images = $this->essay_image_service->getByEssayId($essay->getId());
        if (!$onlyText && $images !== []) {
            foreach ($images as $essay_image) {
                $image = $this->getPageImage((string) $essay_image->getId());
                $path = $this->pdf_creator->getPageImagePathForPdf($image);
                $pdfParts[] = $this->pdf_creator->createStandardPart([
                    new PdfImage(
                        $path,
                        $pdfSettings->getLeftMargin(),
                        $pdfSettings->getContentTopMargin(),
                        210 // A4
                            - $pdfSettings->getLeftMargin() - $pdfSettings->getRightMargin(),
                        297 // A4
                            - $pdfSettings->getContentTopMargin() - $pdfSettings->getContentBottomMargin()
                    )
                ], $pdfSettings);
            }
        } else {
            $writingSettings = $this->writing_settings->get();
            $html = $this->html_processing->processWrittenText($essay, $writingSettings, true);
            $pdfParts[] = $this->pdf_creator->createStandardPart([
                new PdfHtml(
                    $html,
                    $pdfSettings->getLeftMargin() + $writingSettings->getLeftCorrectionMargin(),
                    $pdfSettings->getContentTopMargin(),
                    210 // A4
                        - $pdfSettings->getLeftMargin() - $pdfSettings->getRightMargin()
                        - $writingSettings->getLeftCorrectionMargin() - $writingSettings->getRightCorrectionMargin(),
                    null
                )], $pdfSettings
                    ->withTopMargin($pdfSettings->getTopMargin() + $writingSettings->getTopCorrectionMargin())
                    ->withBottomMargin($pdfSettings->getBottomMargin() + $writingSettings->getBottomCorrectionMargin())
            );
        }

        return $this->pdf_creator->createPdf(
            $pdfParts,
            '', // @todo: Search replacement: $this->context->getSystemName(),
            '', // $task->getWriterName(), // @todo: Same for WritingTask
            '', // $task->getTitle(),
            '' . // , $task->getWriterName() . ' ' .
            $this->format->dateRange($essay->getEditStarted(), $essay->getEditEnded())
        );
    }

    public function getPageImage(string $key): ?ImageDescriptor
    {
        global $DIC;

        $repoImage = $this->essay_image->one((int) $key);

        if (!$repoImage || !$repoImage->getFileId()) {
            return null;
        }

        $stream = $this->storage->getFileStream($repoImage->getFileId());
        $thumb_stream = $repoImage->getThumbId() ?
            $this->storage->getFileStream($repoImage->getFileId()) :
            null;

        return new ImageDescriptor(
            $stream,
            $repoImage->getWidth(),
            $repoImage->getHeight(),
            $repoImage->getMime(),
            // $thumb_stream,
            // $repoImage->getThumbMime(),
            // $repoImage->getThumbWidth(),
            // $repoImage->getThumbHeight()
        );
    }
}
