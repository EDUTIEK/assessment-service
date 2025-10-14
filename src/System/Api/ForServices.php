<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\Entity\Service as EntityService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\Format\FullService as FormatFullService;
use Edutiek\AssessmentService\System\Format\Service as FormatService;
use Edutiek\AssessmentService\System\ImageSketch\ImageMagick\Sketch;
use Edutiek\AssessmentService\System\Language\FullService as LanguageFullService;
use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverterFullService;
use Edutiek\AssessmentService\System\PdfConverter\ServiceByImageMagick;
use Edutiek\AssessmentService\System\PdfConverter\ServiceByGhostscript;
use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreatorFullService;
use Edutiek\AssessmentService\System\PdfCreator\Service as PdfCreatorService;
use Edutiek\AssessmentService\System\ImageSketch\FullService as ImageSketchFullService;
use Edutiek\AssessmentService\System\BackgroundTask\ClientManager as BackgroundTaskManager;
use Edutiek\AssessmentService\System\BackgroundTask\Service as BackgroundTaskService;

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
        $language = $this->internal->language($user_id);
        $timezone ??= $this->config()->getSetup()->getDefaultTimezone();

        return new FormatService($this->dependencies->formatDate(...), $timezone, $language);
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
     * This service is not cached here
     * Components using it must provide a default and a user language code
     * They must add the language files for both
     */
    public function language(): LanguageFullService
    {
        return new LanguageService();
    }

    public function loadLanguagFromFile(int $user_id, string $dir): LanguageFullService
    {
        return $this->internal->loadLanguagFromFile($user_id, $dir);
    }

    public function pdfConverter(): PdfConverterFullService
    {
        return $this->instances[PdfConverterFullService::class] ??= (
            $this->config()->getPathToGhostscript() === null ?
            new ServiceByImageMagick() :
            new ServiceByGhostscript(
                $this->config()->getPathToGhostscript(),
                $this->config()->getSetup()->getAbsoluteTempPath()
            )
        );
    }

    public function pdfCreator(): PdfCreatorFullService
    {
        return $this->instances[PdfCreatorService::class] ??= new PdfCreatorService(
            $this->config()->getSetup()->getAbsoluteTempPath(),
            $this->config()->getSetup()->getRelativeTempPath()
        );
    }

    public function backgroundTask(): BackgroundTaskManager
    {
        return new BackgroundTaskService($this->dependencies->backgroundTaskManager());
    }
}
