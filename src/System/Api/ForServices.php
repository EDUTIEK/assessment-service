<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use Edutiek\AssessmentService\System\Config\Service as ConfigService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\File\Delivery;
use Edutiek\AssessmentService\System\Format\FullService as FormatFullService;
use Edutiek\AssessmentService\System\Format\Service as FormatService;
use Edutiek\AssessmentService\System\ImageSketch\ImageMagick\Sketch;
use Edutiek\AssessmentService\System\Language\FullService as LanguageFullService;
use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\System\User\Service as UserService;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverterFullService;
use Edutiek\AssessmentService\System\PdfConverter\ServiceByImageMagick;
use Edutiek\AssessmentService\System\PdfConverter\ServiceByGhostscript;
use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreatorFullService;
use Edutiek\AssessmentService\System\PdfCreator\Service as PdfCreatorService;
use Edutiek\AssessmentService\System\ImageSketch\FullService as ImageSketchFullService;

class ForServices
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    public function config(): ConfigReadService
    {
        return $this->instances[ConfigReadService::class] ??= new ConfigService(
            $this->dependencies->configRepo(),
            $this->dependencies->setupRepo()
        );
    }

    public function fileStorage(): Storage
    {
        return $this->dependencies->fileStorage();
    }

    public function fileDelivery(): Delivery
    {
        return $this->dependencies->fileDelivery();
    }

    public function format(?string $language = null, ?string $timezone = null): FormatFullService
    {
        $language ??= $this->config()->getSetup()->getDefaultLanguage();
        $timezone ??= $this->config()->getSetup()->getDefaultTimezone();

        return $this->instances[$language][$timezone->getName()] ??= new FormatService($language, $timezone);
    }

    public function user(): UserReadService
    {
        return $this->instances[UserReadService::class] ??= new UserService(
            $this->dependencies->userDataRepo(),
            $this->dependencies->userDisplayRepo()
        );
    }

    public function imageSketch(): ImageSketchFullService
    {
        return $this->instances[ImageSketchFullService::class] ??= new Sketch([
                // Default font of Sketch is not available on Windows - keep default font of Imagick
                'font' => ['name' => null, 'size' => 50]]
        );
    }

    public function language(): LanguageFullService
    {
        return (new LanguageService())->setDefaultLanguage($this->config()->getSetup()->getDefaultLanguage());
    }

    public function pdfConverter(): PdfConverterFullService
    {
        return $this->instances[PdfConverterFullService::class] ??= ($this->config()->getPathToGhostscript() === null ?
            new ServiceByImageMagick() :
            new ServiceByGhostscript(
                $this->config()->getPathToGhostscript(),
                $this->config()->getSetup()->getAbsoluteTempPath()
            )
        );
    }

    public function pdfCreator(): PdfCreatorFullService
    {
        return $this->instances[PdfConverterFullService::class] ??= new PdfCreatorService(
            $this->config()->getSetup()->getAbsoluteTempPath(),
            $this->config()->getSetup()->getRelativeTempPath()
        );
    }


}
