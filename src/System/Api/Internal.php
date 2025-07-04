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

use Edutiek\AssessmentService\System\Language\FullService as LanguageFullService;
use Edutiek\AssessmentService\System\Language\Service as LanguageService;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\System\User\Service as UserService;
use Edutiek\AssessmentService\System\Config\ReadService as ConfigReadService;
use Edutiek\AssessmentService\System\Config\Service as ConfigService;

class Internal
{
    private array $instances = [];

    public function __construct(private readonly Dependencies $dependencies)
    {
    }

    public function language(int $user_id): LanguageFullService
    {
        return $this->instances[LanguageService::class][$user_id] ??=
            $this->loadLanguagFromFile($user_id, __DIR__ . '/../Languages/');
    }

    public function loadLanguagFromFile(int $user_id, string $dir): LanguageFullService
    {
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

        return $service;
    }

    public function config(): ConfigReadService
    {
        return $this->instances[ConfigReadService::class] ??= new ConfigService(
            $this->dependencies->configRepo(),
            $this->dependencies->setupRepo()
        );
    }

    public function user(): UserReadService
    {
        return $this->instances[UserReadService::class] ??= new UserService(
            $this->dependencies->userDataRepo(),
            $this->dependencies->userDisplayRepo()
        );
    }
}
