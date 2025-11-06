<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;

/**
 * Common Api for all components used by the assessment (including itself)
 */
interface ComponentApi
{
    /**
     * Get the Writer AppBridge of the component
     */
    public function writerBridge(int $ass_id, int $user_id): ?AppBridge;

    /**
     * Get the Corrector AppBridge of the component
     */
    public function correctorBridge(int $ass_id, int $user_id): ?AppBridge;

    /**
     * Get the PDF part provider for the writing PDF
     */
    public function writingPartProvider(int $ass_id, int $user_id): ?PdfPartProvider;

    /**
     * Get the PDF part provider for the correction PDF
     */
    public function correctionPartProvider(int $ass_id, int $user_id): ?PdfPartProvider;
}
