<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Api;

use DateTimeZone;
use Edutiek\AssessmentService\System\BackgroundTask\ClientManager as BackgroundTaskManager;
use Edutiek\AssessmentService\System\BackgroundTask\Service as BackgroundTaskService;
use Edutiek\AssessmentService\System\Config\Service as ConfigService;
use Edutiek\AssessmentService\System\ConstraintHandling\Collector;
use Edutiek\AssessmentService\System\ConstraintHandling\CommonCollector;
use Edutiek\AssessmentService\System\ConstraintHandling\ProviderFactory;
use Edutiek\AssessmentService\System\Entity\Service as EntityService;
use Edutiek\AssessmentService\System\Format\Service as FormatService;
use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\PdfConverter\FullService as PdfConverterFullService;
use Edutiek\AssessmentService\System\PdfConverter\ServiceByGhostscript;
use Edutiek\AssessmentService\System\PdfConverter\ServiceByImageMagick;
use Edutiek\AssessmentService\System\PdfCreator\FullService as PdfCreatorFullService;
use Edutiek\AssessmentService\System\PdfCreator\Service as PdfCreatorService;
use Edutiek\AssessmentService\System\PdfProcessing\FullService as PdfProcessingService;
use Edutiek\AssessmentService\System\PdfProcessing\Service as PdfProcessing;
use Edutiek\AssessmentService\System\Transform\Service as TransformService;
use Edutiek\AssessmentService\System\User\Service as UserService;
use Dompdf\Dompdf;

class Internal
{
    private array $instances = [];

    public function __construct(private readonly Dependencies $dependencies)
    {
    }

    public function config(): ConfigService
    {
        return $this->instances[ConfigService::class] ??= new ConfigService(
            $this->dependencies->configRepo(),
            $this->dependencies->setupRepo()
        );
    }

    public function entity(): EntityService
    {
        return $this->instances[EntityService::class] ??= new EntityService();
    }

    public function format(int $user_id, ?DateTimeZone $timezone = null): FormatService
    {
        $timezone ??= $this->config()->getSetup()->getDefaultTimezone();

        return $this->instances[FormatService::class][$user_id][$timezone->getName()] ??= new FormatService(
            $this->dependencies->formatDate(...),
            $timezone,
            $this->language($user_id,  __DIR__ . '/../Languages/'));
    }

    public function language(int $user_id, string $dir): LanguageService
    {
        if (!isset($this->instances[LanguageService::class][$user_id][$dir])) {
            $default_code = $this->config()->getSetup()->getDefaultLanguage();
            $user_code = $this->user()->getUser($user_id)?->getLanguage() ?? $default_code;

            $service = (new LanguageService())
                ->setDefaultLanguage($user_code)
                ->setLanguage($user_code);

            foreach (array_unique([$default_code, $user_code]) as $code) {
                $file = rtrim($dir, '/') . '/' . $code . '.php';
                if (file_exists($file)) {
                    $service->addLanguage($code, require($file));
                }
            }

            $this->instances[LanguageService::class][$user_id][$dir] = $service;
        }

        return $this->instances[LanguageService::class][$user_id][$dir];
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

    public function pdfCreator(): PdfCreatorService
    {
        return $this->instances[PdfCreatorService::class] ??= new PdfCreatorService(
            $this->config()->getSetup()->getAbsoluteTempPath(),
            $this->config()->getSetup()->getRelativeTempPath(),
            fn() => new Dompdf(),
        );
    }

    public function backgroundTask(): BackgroundTaskManager
    {
        return new BackgroundTaskService($this->dependencies->backgroundTaskManager());
    }

    public function pdfProcessing(): PdfProcessing
    {
        return $this->instances[PdfProcessing::class] ??= new PdfProcessing(
            $this->pdfCreator(),
            $this->pdfConverter(),
            $this->dependencies->fileStorage(),
            exec('which pdflatex'),
            exec('which pdftk'),
            $this->config()->getSetup()->getAbsoluteTempPath(),
        );
    }

    public function transform(): TransformService
    {
        return $this->instances[TransformService::class] ??= new TransformService();
    }

    public function user(): UserService
    {
        return $this->instances[UserService::class] ??= new UserService(
            $this->dependencies->userDataRepo(),
            $this->dependencies->userDisplayRepo()
        );
    }
}
