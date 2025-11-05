<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Dompdf\Dompdf;
use Edutiek\AssessmentService\System\BackgroundTask\ClientManager as BackgroundTaskManager;
use Edutiek\AssessmentService\System\BackgroundTask\Service as BackgroundTaskService;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\Entity\Service as EntityService;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Format\FullService as FormatFullService;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessingFullService;
use Edutiek\AssessmentService\System\HtmlProcessing\Service as HtmlProcessingService;
use Edutiek\AssessmentService\System\ImageSketch\FullService as ImageSketchFullService;
use Edutiek\AssessmentService\System\ImageSketch\ImageMagick\Sketch;
use Edutiek\AssessmentService\System\Language\FullService as LanguageFullService;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverterFullService;
use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreatorFullService;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessingService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;

class ForServices
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function config(): ConfigReadService
    {
        return $this->internal->config();
    }

    public function entity(): EntityFullService
    {
        return $this->instances[EntityService::class] ??= new EntityService();
    }

    public function fileStorage(): Storage
    {
        return $this->dependencies->fileStorage();
    }

    public function fileDelivery(): Delivery
    {
        return $this->dependencies->fileDelivery();
    }

    public function format(int $user_id, ?string $timezone = null): FormatFullService
    {
        return $this->internal->format($user_id, $timezone);
    }

    public function htmlProcessing(): HtmlProcessingFullService
    {
        return new HtmlProcessingService();
    }

    public function user(): UserReadService
    {
        return $this->internal->user();
    }

    public function imageSketch(): ImageSketchFullService
    {
        return $this->instances[ImageSketchFullService::class] ??= new Sketch(
            [
                // Default font of Sketch is not available on Windows - keep default font of Imagick
                'font' => ['name' => null, 'size' => 50]
            ]
        );
    }

    /**
     * Service vor translating language variables
     * Components using it must provide language files in the given directory (absolue path)
     * A language file is named by the laoguage code, e.g. de.php
     * it must return a php array with key/value pairs of language variables
     */
    public function language(int $user_id, string $dir): LanguageFullService
    {
        return $this->internal->language($user_id, $dir);
    }

    public function pdfConverter(): PdfConverterFullService
    {
        return $this->internal->pdfConverter();
    }

    public function pdfCreator(): PdfCreatorFullService
    {
        return $this->internal->pdfCreator();
    }

    public function backgroundTask(): BackgroundTaskManager
    {
        return new BackgroundTaskService($this->dependencies->backgroundTaskManager());
    }

    public function pdfProcessing(): PdfProcessingService
    {
        return $this->internal->pdfProcessing();
    }
}
