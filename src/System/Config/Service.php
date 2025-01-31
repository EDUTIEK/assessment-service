<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Api\Dependencies;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Data\ConfigRepo;
use Edutiek\AssessmentService\System\Data\Setup;
use Edutiek\AssessmentService\System\Data\SetupRepo;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private ConfigRepo $config_repo,
        private SetupRepo $setup_repo
    ) {
    }

    public function getConfig(): Config
    {
        return $this->config_repo->get();
    }

    public function saveConfig(Config $config): void
    {
        $this->config_repo->save($config);
    }

    public function getSetup(): Setup
    {
        return $this->setup_repo->get();
    }

    public function getFrontendUrl(FrontendModule $module): string
    {
        switch ($module) {
            case FrontendModule::WRITER:
                return $this->getConfig()->getWriterUrl() ?? $this->buildFrontendUrl($module);
            case FrontendModule::CORRECTOR:
                return $this->getConfig()->getCorrectorUrl() ?? $this->buildFrontendUrl($module);
        }
        return '';
    }

    public function getPathToGhostscript(): ?string
    {
        return $this->getConfig()->getPathToGhostscript() ?? $this->getSetup()->getDefaultPathToGhostscript();
    }

    /**
     * Build the URL of a frontend web app
     * Add a query string with the revision to avoid an outdated cached app
     * @param string $module name and path of the web app in node_modules
     */
    private function buildFrontendUrl(FrontendModule $module): string
    {
        $json = json_decode(file_get_contents(__DIR__ . '/../../../package-lock.json'), true);
        $resolved = $json['packages']['node_modules/' . $module->value]['resolved'] ?? '';
        $revision = (string) parse_url($resolved, PHP_URL_FRAGMENT);

        return $this->getSetup()->getFrontendsBaseUrl()
            . '/' . $module->value . '/' . '/dist/index.html?' . substr($revision, 0, 7);
    }
}
