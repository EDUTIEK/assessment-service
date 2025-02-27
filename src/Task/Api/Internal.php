<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

class Internal
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    /**
     * Translation of language variables
     */
    public function language(string $code) : LanguageService
    {
        return $this->instances[LanguageService::class][$code] = $this->dependencies->systemApi()->language()
            ->addLanguage('de', require(__DIR__ . '/../Languages/de.php'))
            ->setLanguage($code);
    }
}