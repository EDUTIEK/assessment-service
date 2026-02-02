<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImage;

use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\EssayImage;
use Edutiek\AssessmentService\EssayTask\Data\EssayImageRepo;
use Edutiek\AssessmentService\EssayTask\Data\EssayRepo;
use Edutiek\AssessmentService\EssayTask\PdfCreation\WritingProvider;
use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\Data\ImageSizeType;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverter;

readonly class Service implements FullService
{
    public function __construct(
        private EssayImageRepo $image_repo,
        private EssayRepo $essay_repo,
        private Storage $storage,
        private PdfConverter $pdf_converter,
        private WritingProvider $pdf_provider,
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
        if ($essay->getWrittenText() && !$essay->hasPdfFromWrittenText()) {
            $delete_me = $this->pdf_provider->renderEssay($essay, true, false, false);
            $pdfs[] = $this->storage->getFileStream($delete_me);
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

        $this->storage->deleteFile($delete_me);
        $this->purgeFiles($this->image_repo->replaceByEssayId($essay->getId(), $repo_images));
        return $repo_images;
    }

    public function deleteByEssayId(int $essay_id): void
    {
        $this->purgeFiles($this->image_repo->deleteByEssayId($essay_id));
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
