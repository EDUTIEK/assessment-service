<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;

/**
 * Common API for components used by the Assessment component
 *
 * - defines services that contribute to the REST interfaces and to the PDF generation
 * - must be implemented by Assessment (Internal),
 * - must be implemented by EssayTask (extended by TaskApi)
 * - must be implemented by all task types (extended by TypeApi)
 * - is not implemented by System
 * - is internally called by Assessment
 */
interface ComponentApi
{
    /**
     * Get the bridge to provide and process data for the Writer web app
     */
    public function writerBridge(int $ass_id, int $user_id): ?AppBridge;

    /**
     * Get the bridge to provide and process data for the Writer web app
     */
    public function correctorBridge(int $ass_id, int $user_id): ?AppBridge;

    /**
     * Get the provider of parts for the PDF of a writing
     */
    public function writingPartProvider(int $ass_id, int $user_id): ?PdfPartProvider;

    /**
     * Get the provider of parts for the PDF of a correction
     */
    public function correctionPartProvider(int $ass_id, int $user_id): ?PdfPartProvider;
}
