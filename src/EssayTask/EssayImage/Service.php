<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EssayImage;

use LongEssayPDFConverter\PDFImage;
use Edutiek\AssessmentService\EssayTask\Data\EssayImageRepo;
use Edutiek\AssessmentService\Assessment\Data\WriterRepo;
use Edutiek\AssessmentService\EssayTask\PdfOutput\FullService as PdfOutput;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\EssayRepo;

class Service implements FullService
{
    public function __construct(
        private readonly PDFImage $pdf_image,
        private readonly EssayImageRepo $essay_image,
        private readonly PdfOutput $pdf_output,
        private readonly Storage $storage,
        private readonly EssayRepo $repo,
    )
    {
    }

    public function getByEssayId(int $id): array
    {
        $essay = $this->repo->one($id);
        if ($essay->getPdfVersion() === null) {
            return [];
        }

        $images = $this->essay_image->allByEssayId($essay->getId());
        if ($images !== []) {
            return $images;
        }

        $this->createByEssayId($essay);
        return $this->essay_image->allByEssayId($essay->getId());
    }

    public function createByEssayId(Essay $essay): int
    {
        $delete_me = null;
        $pdfs = [];
        if ($essay->getPdfVersion()) {
            if (false && $essay->getWrittenText()) {
                // @todo: Anonymous = true
                $delete_me = $this->storage->saveFile(
                    $this->pdf_output->getWritingAsPdf($essay, true, true),
                    null
                )->getId();
                $pdfs[] = $this->storage->getFileStream($delete_me);
            }

            $stream = $this->storage->getFileStream($essay->getPdfVersion());
            if ($stream !== null) {
                $pdfs[] = $stream;
            }
        }

        if ($pdfs === []) {
            return 0;
        }

        $page_images = $this->createPageImagesFromPdfs($pdfs);
        $repo_images = [];

        $page = 1;
        foreach ($page_images as $image) {
            $file_id = $this->storage->saveFile($image['stream'], null)->getId();

            $thumb_id = $image['thumb-stream'] ?
                $this->storage->saveFile($image['thumb-stream'], null)->getId() :
                null;

            $repo_images[] = $this->essay_image->new()
                ->setEssayId($essay->getId())
                ->setPageNo($page++)
                ->setFileId($file_id)
                ->setMime($image['type'])
                ->setWidth($image['width'])
                ->setHeight($image['height'])
                ->setThumbId($thumb_id)
                ->setThumbMime($image['thumb-type'])
                ->setThumbWidth($image['thumb-width'])
                ->setThumbHeight($image['thumb-height']);
        }

        $this->storage->deleteFile($delete_me);
        // @todo no replacement found for this function in EssayRepo
        // this is an atomic operation to avoid race conditions between background task and creation on demand
        // $deleted = $this->essayRepo->replaceEssayImagesByEssayId($essay->getId(), $repo_images);
        // $this->purgeFiles($deleted);

        return count($repo_images);
    }

    private function createPageImagesFromPdfs(array $pdfs) : array
    {
        $images = [];
        foreach ($pdfs as $pdf) {
            $images = array_merge($images,  $this->createImagesFromPdf($pdf));
        }
        return $images;
    }

    private function createImagesFromPdf($pdf) : array
    {
        $images = [];
        $page_descriptors = $this->pdf_image->asOnePerPage($pdf, PDFImage::NORMAL);
        $thumbnail_descriptors = $this->pdf_image->asOnePerPage($pdf, PDFImage::THUMBNAIL);

        foreach ($page_descriptors as $index => $page_desc) {
            $thumb_desc = $thumbnail_descriptors[$index] ?? null;

            $images[] = [ // new PageImage
                'stream' => $page_desc->stream(),
                'type' => $page_desc->type(),
                'width' => $page_desc->width(),
                'height' => $page_desc->height(),
                'thumb-stream' => $thumb_desc?->stream(),
                'thumb-type' => $thumb_desc?->type(),
                'thumb-width' => $thumb_desc?->width(),
                'thumb-height' => $thumb_desc?->height()
            ];
        }

        return $images;
    }

    public function deleteByEssayId(int $essay_id): void
    {
        // @todo: replaceImagesByEssayId has no replacement in the Repository spreadsheet.
        // this is an atomic operation to avoid race conditions between background task and deletion
        // $deleted = $this->essay->replaceEssayImagesByEssayId($essay_id, []);
        // $this->purgeFiles($deleted);
    }

    private function purgeFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->storage->deleteFile($file->getFileId());
            $this->storage->deleteFile($file->getThumbId());
        }
    }
}
