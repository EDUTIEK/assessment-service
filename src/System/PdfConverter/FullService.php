<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\PdfConverter;

use Edutiek\AssessmentService\System\Data\ImageDescriptor;
use Edutiek\AssessmentService\System\Data\ImageSizeType;

interface FullService
{
    /**
     * Returns an image for each page in the given PDF.
     *
     * @param resource $pdf
     * @return list<ImageDescriptor>
     */
    public function asOnePerPage($pdf, ImageSizeType $size = ImageSizeType::THUMBNAIL): array;

    /**
     * Returns an image of all PDF pages appended below each other.
     *
     * @param resource $pdf
     */
    public function asOne($pdf, ImageSizeType $size = ImageSizeType::THUMBNAIL): ?ImageDescriptor;
}