<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImage;

use Edutiek\AssessmentService\EssayTask\Data\EssayImage;
use Edutiek\AssessmentService\EssayTask\Data\EssayImageRepo;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\EssayRepo;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverter;
use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreator;
use Edutiek\AssessmentService\System\Data\ImageSizeType;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\PdfCreator\PlainSettings;
use Edutiek\AssessmentService\EssayTask\Data\WritingSettings;
use Edutiek\AssessmentService\System\PdfCreator\PdfPart;
use Edutiek\AssessmentService\System\PdfCreator\PdfHtml;

readonly class Service implements FullService
{
    public function __construct(
        private EssayImageRepo $image_repo,
        private EssayRepo $essay_repo,
        private WritingSettings $writing_settings,
        private Storage $storage,
        private PdfConverter $pdf_converter,
        private PdfCreator $pdf_creator,
        private HtmlProcessing $html_processing,
    ) {
    }

    public function getByEssayId(int $essay_id): array
    {
        $essay = $this->essay_repo->one($essay_id);
        if ($essay?->getPdfVersion() === null) {
            return [];
        }

        $images = $this->image_repo->allByEssayId($essay_id);
        if ($images !== []) {
            return $images;
        }
        return $this->createForEssay($essay);
    }

    public function createForEssay(Essay $essay): array
    {
        $delete_me = null;
        $pdfs = [];
        if ($essay->getWrittenText()) {
            $delete_me = $this->storage->saveFile($this->createPdfFromWrittenText($essay), null);
            $pdfs[] = $this->storage->getFileStream($delete_me->getId());
        }
        if ($essay->getPdfVersion()) {
            $stream = $this->storage->getFileStream($essay->getPdfVersion());
            if ($stream !== null) {
                $pdfs[] = $stream;
            }
        }

        if ($pdfs === []) {
            return [];
        }

        $page_images = $this->createPageImagesFromPdfs($pdfs);
        $repo_images = [];

        $page = 1;
        foreach ($page_images as $image) {
            list($file, $thumb) = $image;

            $file_id = $page ? $this->storage->saveFile($file->stream(), null)?->getId() : null;
            $thumb_id = $thumb ? $this->storage->saveFile($thumb->stream(), null)->getId() : null;

            $repo_images[] = $this->image_repo->new()
                                              ->setEssayId($essay->getId())
                                              ->setPageNo($page++)
                                              ->setFileId($file_id)
                                              ->setMime($file->type())
                                              ->setWidth($file->width())
                                              ->setHeight($file->height())
                                              ->setThumbId($thumb_id)
                                              ->setThumbMime($thumb->type())
                                              ->setThumbWidth($thumb->width())
                                              ->setThumbHeight($thumb->height());
        }

        $this->storage->deleteFile($delete_me?->getId());
        $this->purgeFiles($this->image_repo->replaceByEssayId($essay->getId(), $repo_images));
        return $repo_images;
    }


    public function deleteByEssayId(int $essay_id): void
    {
        $this->purgeFiles($this->image_repo->deleteByEssayId($essay_id));
    }


    private function createPdfFromWrittenText(Essay $essay)
    {
        $pdf_settings = new PlainSettings();
        $html = $this->html_processing->processWrittenText($essay, $this->writing_settings, true);

        $element = new PdfHtml(
            $html,
            $pdf_settings->getLeftMargin() + $this->writing_settings->getLeftCorrectionMargin(),
            $pdf_settings->getContentTopMargin(),
            210 // A4
            - $pdf_settings->getLeftMargin() - $pdf_settings->getRightMargin()
            - $this->writing_settings->getLeftCorrectionMargin() - $this->writing_settings->getRightCorrectionMargin(),
            null
        );
        $part = (new PdfPart(
            PdfPart::FORMAT_A4,
            PdfPart::ORIENTATION_PORTRAIT,
            [$element]
        ))  ->withTopMargin($pdf_settings->getTopMargin())
             ->withBottomMargin($pdf_settings->getBottomMargin())
             ->withLeftMargin($pdf_settings->getLeftMargin() + $this->writing_settings->getLeftCorrectionMargin())
             ->withRightMargin($pdf_settings->getRightMargin() + $this->writing_settings->getRightCorrectionMargin())
             ->withHeaderMargin($pdf_settings->getHeaderMargin())
             ->withFooterMargin($pdf_settings->getFooterMargin())
             ->withPrintHeader($pdf_settings->getAddHeader())
             ->withPrintFooter($pdf_settings->getAddFooter());

        return $this->pdf_creator->createPdf([$part]);
    }


    /**
     * @param resource[] $pdfs
     * @return array<ImageDescriptor, ImageDescriptor>[]
     */
    private function createPageImagesFromPdfs(array $pdfs): array
    {
        $images = [];
        foreach ($pdfs as $pdf) {
            $images = array_merge($images, $this->createImagesFromPdf($pdf));
        }
        return $images;
    }

    /**
     * @param resource $pdf
     * @return array<ImageDescriptor, ImageDescriptor> page image, thumb image
     */
    private function createImagesFromPdf($pdf): array
    {
        $images = [];
        $page_descriptors = $this->pdf_converter->asOnePerPage($pdf, ImageSizeType::NORMAL);
        $thumbnail_descriptors = $this->pdf_converter->asOnePerPage($pdf, ImageSizeType::THUMBNAIL);

        foreach ($page_descriptors as $index => $page_desc) {
            $thumb_desc = $thumbnail_descriptors[$index] ?? null;
            $images[] = [$page_desc, $thumb_desc];
        }
        return $images;
    }

    /**
     * Purge the image files of already deleted images
     * @param EssayImage[] $images
     */
    private function purgeFiles(array $images): void
    {
        foreach ($images as $image) {
            $this->storage->deleteFile($image->getFileId());
            $this->storage->deleteFile($image->getThumbId());
        }
    }
}
